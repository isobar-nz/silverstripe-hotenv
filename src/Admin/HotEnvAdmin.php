<?php

namespace Admin;

use IsobarNZ\HotEnv\HotEnv;
use M1\Env\Exception\ParseException;
use M1\Env\Parser;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Assets\Filesystem;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

class HotEnvAdmin extends LeftAndMain
{
    /**
     * @config
     * @var string
     */
    private static $menu_title = 'System Environment';

    /**
     * @config
     * @var string
     */
    private static $url_segment = 'hotenv';

    /**
     * @param Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }
        if (!$member) {
            return false;
        }

        // only default admin can use this section
        return Permission::checkMember($member, 'ADMIN') && $member->isDefaultAdmin();
    }

    public function getEditForm($id = null, $fields = null)
    {
        $fields = FieldList::create(
            TextField::create('HotEnvPath', 'Path')->performReadonlyTransformation(),
            TextareaField::create('HotEnv', '.hotenv file content')
        );

        $actions = FieldList::create(
            FormAction::create('save', _t('SilverStripe\\CMS\\Controllers\\CMSMain.SAVE', 'Save'))
                ->addExtraClass('btn btn-primary')
                ->addExtraClass('font-icon-add-circle')
        );

        $negotiator = $this->getResponseNegotiator();
        $form = Form::create(
            $this,
            "EditForm",
            $fields,
            $actions
        )->setHTMLID('Form_EditForm');
        $form->addExtraClass('cms-edit-form');
        $form->setTemplate($this->getTemplatesWithSuffix('_EditForm'));
        $form->setAttribute('data-pjax-fragment', 'CurrentForm');
        $form->setValidationResponseCallback(function (ValidationResult $errors) use ($negotiator, $form) {
            $request = $this->getRequest();
            if ($request->isAjax() && $negotiator) {
                $result = $form->forTemplate();

                return $negotiator->respond($request, array(
                    'CurrentForm' => function () use ($result) {
                        return $result;
                    }
                ));
            }
            return null;
        });

        // Load content
        $path = HotEnv::getPath();
        $data = ['HotEnvPath' => $path];
        if (file_exists($path)) {
            $data['HotEnv'] = file_get_contents($path);
        }
        $form->loadDataFrom($data);

        $form->addExtraClass('fill-height');

        // @todo - dotenv validator
        return $form;
    }

    /**
     * Save .hotenv content
     *
     * @param array $data
     * @param Form  $form
     * @return HTTPResponse
     * @throws HTTPResponse_Exception
     * @throws ValidationException
     */
    public function save($data, $form)
    {
        $request = $this->getRequest();

        // Validate content, turn parse errors into validation errors
        try {
            Parser::parse($data['HotEnv']);
        } catch (ParseException $ex) {
            $result = ValidationResult::create();
            $result->addFieldError('HotEnv', $ex->getMessage());
            throw new ValidationException($result);
        }

        // Ensure path exists
        $path = HotEnv::getPath();
        Filesystem::makeFolder(dirname($path));

        file_put_contents($path, $data['HotEnv']);

        $message = _t(__CLASS__ . '.SAVEDUP', 'Saved.');
        $response = $this->getResponseNegotiator()->respond($request);

        $response->addHeader('X-Status', rawurlencode($message));
        return $response;
    }
}

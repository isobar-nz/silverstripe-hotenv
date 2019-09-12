<?php

# .env file inclusion tool

// Note: We must require_once the framework include before this.

use IsobarNZ\HotEnv\HotEnv;
use SilverStripe\Core\Environment;
use SilverStripe\Core\EnvironmentLoader;

call_user_func(function () {
    $paths = [
        // Module in top level folder, framework in vendor
        __DIR__ . '/../../../vendor/silverstripe/framework/src/includes/constants.php',
        // Module in vendor, framework in vendor
        __DIR__ . '/../../../../silverstripe/framework/src/includes/constants.php',
        // Framework in root, module in vendor
        __DIR__ . '/../../../../../src/includes/constants.php',
        // Module in root, framework in vendor
        __DIR__ . '/../../vendor/silverstripe/framework/src/includes/constants.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            break;
        }
    }

    // Note: core .env may super-override hotenv
    if (Environment::getEnv('HOTENV_IGNORE')) {
        return;
    }

    // Load file from path
    $hotenvPath = HotEnv::getPath();
    if ($hotenvPath && file_exists($hotenvPath)) {
        $loader = new EnvironmentLoader();
        $loader->loadFile($hotenvPath);

        // Success!
        define('HOTENV_LOADED', true);
    }

});

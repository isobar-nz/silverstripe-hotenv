<?php

namespace IsobarNZ\HotEnv;

use SilverStripe\Core\Environment;
use SilverStripe\Core\Path;

class HotEnv
{
    /**
     * Get path to .hotenv file
     *
     * @return string
     */
    public static function getPath()
    {
        // Load file from path
        $hotenvPath = Environment::getEnv('HOTENV_PATH');

        // Resolve relative paths (e.g. ./assets/.protected/.hotenv)
        if ($hotenvPath && stripos($hotenvPath, './') === 0) {
            $hotenvPath = Path::join(
                BASE_PATH,
                $hotenvPath
            );
        }

        // Pick default path if not set
        if (!$hotenvPath) {
            $hotenvPath = Path::join(ASSETS_PATH, '.protected/.hotenv');
        }

        return $hotenvPath;
    }
}

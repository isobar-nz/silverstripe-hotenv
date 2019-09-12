## hotenv

This lets you edit environment settings in the CMS, which allows you to quickly
stage or test different environment settings without having to do a full deployment.

This module has a few restrictions:

 - Environment can only be modified by the default admin (specified in the core .env)
 - The core .env file cannot be modified; You can only specify additional variables
   to add or override
 - The additional file will be ignored if `HOTENV_IGNORE` is specified in the root

Variables and constants of note:

 - HOTENV_PATH - Environment variable specified in the root .env to the location (writable) where
   your `.hotenv` file will be located. By default it will write to `public/assets/.protected/.hotenv`,
   but it's better to put this outside of the webroot.
   E.g. `HOTENV_PATH="./system/.hotenv` will put a folder in base called `system` and save your `.hotenv`
   file there.
 - HOTENV_IGNORE - Environment variable to disable hotenv loading (you can still edit the file however)
 - HOTENV_LOADED - Constant (not environment variable) that is set if the `.hotenv` file was successfully
   loaded in the current request

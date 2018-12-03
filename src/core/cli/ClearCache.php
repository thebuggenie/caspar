<?php

    namespace caspar\core\cli;

    use caspar\core\CliCommand;

    /**
     * CLI command class, main -> clear-cache
     *
     * @package caspar
     * @subpackage core
     */
    class ClearCache extends CliCommand
    {
        public function getCommandAliases()
        {
            return [
                'cc'
            ];
        }

        protected function _setup()
        {
            $this->_command_name = 'clear-cache';
            $this->_description = "Clears the local cache";
        }

        protected function verifyCachePath()
        {
            if (is_dir(CASPAR_CACHE_PATH)) {
                return;
            }

            if (!is_dir(CASPAR_APPLICATION_PATH)) {
                $this->cliEcho('Caspar application path does not exist. Try using the ');
                $this->cliEcho('create_app', 'white', 'bold');
                $this->cliEcho(" command before continuing.\n");

                exit();
            }

            $current_umask = umask(0);
            mkdir(CASPAR_CACHE_PATH, 0777);
            umask($current_umask);
        }

        public function do_execute()
        {
            $this->verifyCachePath();

            $this->cliEcho('Removing cache files from ' . CASPAR_CACHE_PATH . "\n");
            foreach (new \DirectoryIterator(CASPAR_CACHE_PATH) as $cacheFile) {
                if (!$cacheFile->isDot()) {
                    $this->cliEcho("Removing {$cacheFile->getFilename()}\n");
                    unlink($cacheFile->getPathname());
                }
            }
            $this->cliEcho("Done!\n");
        }

    }

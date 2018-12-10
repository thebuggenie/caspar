<?php

    namespace caspar\core\cli;

    use caspar\core\CliCommand;

    /**
     * CLI command class, main -> create_app
     *
     * @package caspar
     * @subpackage core
     */
    class CreateModule extends CliCommand
    {

        protected $module_name = null;

        protected function _setup()
        {
            $this->_command_name = 'create_module';
            $this->_description = 'Creates the default folder skeleton for a module';
            $this->addRequiredArgument('module_name', 'The name of the module to create. No spaces.');
        }

        public function do_execute()
        {
            $this->module_name = mb_strtolower($this->getProvidedArgument('module_name'));

            $this->cliEcho("\n");
            $this->cliEcho('Creating module ');
            $this->cliEcho($this->module_name, 'white', 'bold');
            $this->cliEcho("\n");

            $current_umask = umask(0);

            $this->createModuleFolders();
            $this->createModuleClasses();
            $this->createModuleFiles();

            umask($current_umask);

            $this->cliEcho("\n");
            $this->cliEcho("Module skeleton was created successfully!\n", 'green');
            $this->cliEcho("\n");
        }

        protected function createModuleFolders(): void
        {
            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . $this->module_name)) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . $this->module_name, 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . $this->module_name, 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . $this->module_name . DS . 'cli')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . $this->module_name . DS . 'cli', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . $this->module_name . DS . 'cli', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . $this->module_name . DS . 'controllers')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . $this->module_name . DS . 'controllers', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . $this->module_name . DS . 'controllers', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . $this->module_name . DS . 'templates')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . $this->module_name . DS . 'templates', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . $this->module_name . DS . 'templates', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

        }

        protected function createModuleClasses(): void
        {
            $uppercase_module = ucfirst($this->module_name);
            $module_class_file = CASPAR_MODULES_PATH . $this->module_name . DS . $uppercase_module . '.php';
            $module_controller = CASPAR_MODULES_PATH . $this->module_name . DS . 'controllers' . DS . 'Main.php';
            $module_components = CASPAR_MODULES_PATH . $this->module_name . DS . 'Components.php';

            if (!file_exists($module_class_file)) {
                file_put_contents($module_class_file, str_replace(['modulename', 'DefaultModule'], [$this->module_name, $uppercase_module], file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'DefaultModule.php')));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . $this->module_name . DS, 'white', 'bold');
                $this->cliEcho($uppercase_module . '.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($module_controller)) {
                file_put_contents($module_controller, str_replace(['modulename', 'DefaultController'], [$this->module_name, 'Main'], file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'DefaultController.php')));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . $this->module_name . DS . 'controllers' . DS, 'white', 'bold');
                $this->cliEcho('Main.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($module_components)) {
                file_put_contents($module_components, str_replace(['modulename', 'DefaultComponents'], [$this->module_name, 'Components'], file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'DefaultComponents.php')));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . $this->module_name . DS, 'white', 'bold');
                $this->cliEcho('Components.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }
        }

        protected function createModuleFiles(): void
        {
            $index_action_filename = CASPAR_MODULES_PATH . DS . $this->module_name . DS . 'templates' . DS . 'index.html.php';

            if (!file_exists($index_action_filename)) {
                file_put_contents($index_action_filename, "This is the {$this->module_name} index page");

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . $this->module_name . DS . 'templates' . DS, 'white', 'bold');
                $this->cliEcho('index.html.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }
        }

    }

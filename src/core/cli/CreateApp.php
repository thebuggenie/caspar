<?php

    namespace caspar\core\cli;

    use caspar\core\CliCommand;

    /**
     * CLI command class, main -> create_app
     *
     * @package thebuggenie
     * @subpackage core
     */
    class CreateApp extends CliCommand
    {

        protected $app_key = null;

        protected function _setup()
        {
            $this->_command_name = 'create_app';
            $this->_description = "Creates basic application skeleton";
            $this->addRequiredArgument('application_name', "The app name to create, typically 'MyApp' or similar - no spaces!");
        }

        public function do_execute()
        {
            $this->app_key = mb_strtolower($this->getProvidedArgument('application_name'));

            $app_name = ucfirst($app_key);
            $module_description = "Autogenerated module {$app_name}";

            $this->cliEcho("Initializing ");
            $this->cliEcho("{$app_key}\n", 'white', 'bold');
            $this->cliEcho(" application skeleton.\n");

            $current_umask = umask(0);

            $this->createApplicationFolder();
            $this->createApplicationFolders();
            $this->createApplicationConfigurationFile();
            $this->createApplicationFiles();

            umask($current_umask);

            $this->cliEcho("Application skeleton was created successfully!\n", 'green');
        }

        protected function createApplicationConfigurationFile(): void
        {
            $config_filename = CASPAR_APPLICATION_PATH . DS . 'configuration' . DS . 'caspar.yml';
            if (!file_exists($config_filename)) {
                $config = [
                    'core' => [
                        'debug' => false,
                        'base_url' => '',
                        'base_path' => '',
                        'cookie_domain' => '',
                        'cookie_path' => '',
                        'stylesheets' => ['/css/' . $this->app_key . '.css'],
                        'javascripts' => ['/js/' . $this->app_key . '.js'],
                        'user_classname' => '\application\entities\User',
                        'response_classname' => '\caspar\core\Response'
                    ],
                    'services' => [
                        'b2db' => [
                            'auto_initialize' => true,
                            'callback' => ['\b2db\Core', 'initialize'],
                            'arguments' => [
                                'driver' => 'mysql',
                                'hostname' => '',
                                'username' => '',
                                'password' => '',
                                'database' => '',
                                'tableprefix' => ''
                            ]
                        ]
                    ]
                ];

                file_put_contents($config_filename, \Spyc::YAMLDump($config));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'configuration' . DS, 'white', 'bold');
                $this->cliEcho('caspar.yml', 'yellow', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }
        }

        protected function createApplicationFiles(): void
        {
            $template_filename = CASPAR_APPLICATION_PATH . DS . 'templates' . DS . 'layout.php';
            $index_php_filename = CASPAR_APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'index.php';
            $index_action_filename = CASPAR_MODULES_PATH . DS . 'main' . DS . 'templates' . DS . 'index.html.php';
            $notfound_filename = CASPAR_MODULES_PATH . DS . 'main' . DS . 'templates' . DS . 'notfound.html.php';

            if (!file_exists($template_filename)) {
                file_put_contents($template_filename, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'layout.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'templates' . DS, 'white', 'bold');
                $this->cliEcho('layout.php', 'yellow', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!file_exists($index_php_filename)) {
                file_put_contents($index_php_filename, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'index.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('public' . DS, 'white', 'bold');
                $this->cliEcho('index.php', 'yellow', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!file_exists($index_action_filename)) {
                file_put_contents($index_action_filename, 'Hello world');

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS . 'templates' . DS, 'white', 'bold');
                $this->cliEcho('index.html.php', 'yellow', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!file_exists($notfound_filename)) {
                file_put_contents($notfound_filename, 'Hello world');

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS . 'templates' . DS, 'white', 'bold');
                $this->cliEcho('notfound.html.php', 'yellow', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }
        }

        protected function createApplicationClasses(): void
        {
            $login_traits = CASPAR_APPLICATION_PATH . DS . 'traits' . DS . 'LoginFunctions.php';
            $main_controller = CASPAR_MODULES_PATH . DS . 'main' . DS . 'Actions.php';
            $main_components = CASPAR_MODULES_PATH . DS . 'main' . DS . 'Components.php';

            if (!file_exists($main_controller)) {
                file_put_contents($main_controller, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'Actions.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS, 'white', 'bold');
                $this->cliEcho('Actions.php', 'yellow', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!file_exists($main_components)) {
                file_put_contents($main_components, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'Components.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS, 'white', 'bold');
                $this->cliEcho('Components.php', 'yellow', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }
        }

        protected function createApplicationFolders(): void
        {
            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'configuration')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'configuration', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'configuration', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'entities')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'entities', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'entities', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'tables')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'tables', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'entities' . DS . 'tables', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'i18n')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'i18n', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'i18n', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'i18n' . DS . 'en_US')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'i18n' . DS . 'en_US', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'i18n' . DS . 'en_US', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'libs')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'libs', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'libs', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . 'main')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . 'main', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . 'main' . DS . 'cli')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . 'main' . DS . 'cli', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS . 'cli', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . 'main' . DS . 'templates')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . 'main' . DS . 'templates', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS . 'templates', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'templates')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'templates', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'templates', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . '..' . DS . 'public')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . '..' . DS . 'public', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('public', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }
        }

        protected function createApplicationFolder(): void
        {
            if (!is_dir(CASPAR_APPLICATION_PATH)) {
                mkdir(CASPAR_APPLICATION_PATH, 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }
        }

    }

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

            $app_name = ucfirst($this->app_key);

            $this->cliEcho("\n");
            $this->cliEcho("Initializing ");
            $this->cliEcho($this->app_key, 'white', 'bold');
            $this->cliEcho(" application skeleton.\n");

            $current_umask = umask(0);

            $this->createApplicationFolder();
            $this->createApplicationFolders();
            $this->createApplicationClasses();
            $this->createApplicationEntities();
            $this->createApplicationConfigurationFile();
            $this->createApplicationFiles();

            umask($current_umask);

            $this->cliEcho("\n");
            $this->cliEcho("Application skeleton was created successfully!\n", 'green');
            $this->cliEcho("\n");
            $this->cliEcho('The application skeleton includes a dependency on ');
            $this->cliEcho('thebuggenie/b2db', 'white', 'bold');
            $this->cliEcho("\n");
            $this->cliEcho('If you want to use the b2db ORM, run ');
            $this->cliEcho('composer require thebuggenie/b2db', 'white', 'bold');
            $this->cliEcho(" before\ncontinuing, or set ");
            $this->cliEcho('auto_initialize', 'white', 'bold');
            $this->cliEcho(' to ');
            $this->cliEcho('false', 'green');
            $this->cliEcho(' in the ');
            $this->cliEcho('b2db services', 'white', 'bold');
            $this->cliEcho(" section of the\napplication configuration file (");
            $this->cliEcho('application/configuration/caspar.yml', 'green');
            $this->cliEcho(")\n");
            $this->cliEcho("\n");
        }

        protected function createApplicationConfigurationFile(): void
        {
            $config_filename = CASPAR_APPLICATION_PATH . DS . 'configuration' . DS . 'caspar.yml';
            $dev_config_filename = CASPAR_APPLICATION_PATH . DS . 'configuration' . DS . 'caspar_dev.yml';
            if (!file_exists($config_filename)) {
                $config = [
                    'core' => [
                        'debug' => false,
                        'base_url' => '',
                        'base_path' => '',
                        'cookie_domain' => '',
                        'cookie_path' => '',
                        'language' => 'en_US',
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
                            	[
	                                'driver' => 'mysql',
	                                'hostname' => '',
	                                'username' => '',
	                                'password' => '',
	                                'database' => '',
	                                'tableprefix' => ''
	                            ],
	                            ['\caspar\core\Caspar', 'getCache']
                            ]
                        ]
                    ]
                ];
                $dev_config = [
                    'core' => [
                        'debug' => true,
                        ]
                    ];

                file_put_contents($config_filename, \Spyc::YAMLDump($config));
                file_put_contents($dev_config_filename, \Spyc::YAMLDump($dev_config));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'configuration' . DS, 'white', 'bold');
                $this->cliEcho('caspar.yml', 'yellow', 'bold');
                $this->cliEcho(" configuration file\n", 'green');

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'configuration' . DS, 'white', 'bold');
                $this->cliEcho('caspar_dev.yml', 'yellow', 'bold');
                $this->cliEcho(" configuration file (development)\n", 'green');
            }
        }

        protected function createApplicationFiles(): void
        {
            $template_filename = CASPAR_APPLICATION_PATH . DS . 'templates' . DS . 'layout.php';
            $index_php_filename = CASPAR_APPLICATION_PATH . DS . '..' . DS . 'public' . DS . 'index.php';
            $htaccess_filename = CASPAR_APPLICATION_PATH . DS . '..' . DS . 'public' . DS . '.htaccess';
            $index_action_filename = CASPAR_MODULES_PATH . DS . 'main' . DS . 'templates' . DS . 'index.html.php';
            $notfound_filename = CASPAR_MODULES_PATH . DS . 'main' . DS . 'templates' . DS . 'notfound.html.php';
            $strings_filename = CASPAR_APPLICATION_PATH . DS . 'i18n' . DS . 'en_US' . DS . 'strings.inc.php';

            if (!file_exists($template_filename)) {
                file_put_contents($template_filename, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'layout.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'templates' . DS, 'white', 'bold');
                $this->cliEcho('layout.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($index_php_filename)) {
                file_put_contents($index_php_filename, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'index.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('public' . DS, 'white', 'bold');
                $this->cliEcho('index.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($htaccess_filename)) {
                file_put_contents($htaccess_filename, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'htaccess'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('public' . DS, 'white', 'bold');
                $this->cliEcho('.htaccess', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($index_action_filename)) {
                file_put_contents($index_action_filename, 'Hello world');

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS . 'templates' . DS, 'white', 'bold');
                $this->cliEcho('index.html.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($notfound_filename)) {
                file_put_contents($notfound_filename, 'Hello world');

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS . 'templates' . DS, 'white', 'bold');
                $this->cliEcho('notfound.html.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($strings_filename)) {
                file_put_contents($strings_filename, '<?php' . "\n\n");

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'i18n' . DS . 'en_US' . DS, 'white', 'bold');
                $this->cliEcho('strings.inc.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }
        }

        protected function createApplicationEntities(): void
        {
            $user_class = CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'User.php';
            $user_table_class = CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'tables' . DS . 'Users.php';
            $user_token_class = CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'UserToken.php';
            $user_token_table_class = CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'tables' . DS . 'UserTokens.php';
            $user_session_class = CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'UserSession.php';
            $user_session_table_class = CASPAR_APPLICATION_PATH . DS . 'entities' . DS . 'tables' . DS . 'UserSessions.php';

            if (!file_exists($user_class)) {
                file_put_contents($user_class, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'User.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'entities' . DS, 'white', 'bold');
                $this->cliEcho('User.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($user_table_class)) {
                file_put_contents($user_table_class, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'Users.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'entities' . DS . 'tables' . DS, 'white', 'bold');
                $this->cliEcho('Users.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($user_token_class)) {
                file_put_contents($user_token_class, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'UserToken.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'entities' . DS, 'white', 'bold');
                $this->cliEcho('UserToken.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($user_token_table_class)) {
                file_put_contents($user_token_table_class, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'UserTokens.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'entities' . DS . 'tables' . DS, 'white', 'bold');
                $this->cliEcho('UserTokens.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($user_session_class)) {
                file_put_contents($user_session_class, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'UserSession.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'entities' . DS, 'white', 'bold');
                $this->cliEcho('UserSession.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($user_session_table_class)) {
                file_put_contents($user_session_table_class, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'UserSessions.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'entities' . DS . 'tables' . DS, 'white', 'bold');
                $this->cliEcho('UserSessions.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

        }

        protected function createApplicationClasses(): void
        {
            $login_traits = CASPAR_APPLICATION_PATH . 'traits' . DS . 'LoginFunctions.php';
            $main_controller = CASPAR_MODULES_PATH . 'main' . DS . 'controllers' . DS . 'Main.php';
            $main_components = CASPAR_MODULES_PATH . 'main' . DS . 'Components.php';

            if (!file_exists($login_traits)) {
                file_put_contents($login_traits, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'LoginFunctions.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'traits' . DS, 'white', 'bold');
                $this->cliEcho('LoginFunctions.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($main_controller)) {
                file_put_contents($main_controller, file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'Main.php'));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS . 'controllers' . DS, 'white', 'bold');
                $this->cliEcho('Main.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }

            if (!file_exists($main_components)) {
                file_put_contents($main_components, str_replace(['modulename', 'DefaultComponents'], ['main', 'Components'], file_get_contents(CASPAR_PATH . DS . 'fixtures' . DS . 'DefaultComponents.php')));

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS, 'white', 'bold');
                $this->cliEcho('Components.php', 'yellow', 'bold');
                $this->cliEcho(" file\n", 'green');
            }
        }

        protected function createApplicationFolders(): void
        {
            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'cache')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'cache', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'cache', 'white', 'bold');
                $this->cliEcho(" folder\n", 'green');
            }

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

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . 'main' . DS . 'controllers')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'modules' . DS . 'main' . DS . 'controllers', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'modules' . DS . 'main' . DS . 'controllers', 'white', 'bold');
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

            if (!is_dir(CASPAR_APPLICATION_PATH . DS . 'traits')) {
                mkdir(CASPAR_APPLICATION_PATH . DS . 'traits', 0777);

                $this->cliEcho('* created ', 'green');
                $this->cliEcho('application' . DS . 'traits', 'white', 'bold');
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

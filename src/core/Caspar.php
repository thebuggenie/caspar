<?php

namespace caspar\core;

use caspar\core\exceptions\ActionNotFoundException;
use caspar\core\exceptions\CSRFFailureException;
use caspar\core\exceptions\LibraryNotFoundException;

/**
 * The core class of Caspar
 *
 * @author Daniel Andre Eikeland <zegenie@gmail.com>
 * @version 1.0
 * @license http://www.opensource.org/licenses/mozilla1.1.php Mozilla Public License 1.1 (MPL 1.1)
 * @package caspar
 * @subpackage core
 */

/**
 * The core class of Caspar
 *
 * @package caspar
 * @subpackage core
 */
class Caspar
{

	const CACHE_KEY_ROUTES_ALL = '_routes';
	const CACHE_KEY_ROUTES_APPLICATION = '_routes_premodules';
	const CACHE_KEY_B2DB_CONFIG = '_b2db_config';
	const CACHE_KEY_PERMISSIONS_CACHE = '_permissions';
	const CACHE_KEY_SETTINGS = '_settings';

	static protected $_environment;
	static protected $_debug_mode = true;
	static protected $_partials_visited = [];
	static protected $_configuration;
	static protected $_serviceconfigurations;
	static protected $_services = [];
	static protected $_ver_mj;
	static protected $_ver_mn;
	static protected $_ver_rev;
	static protected $_ver_name;

	/**
	 * @var Cache
	 */
	static protected $_cache;

	/**
	 * The current user
	 *
	 * @var User
	 */
	static protected $_user;

	/**
	 * The include path
	 *
	 * @var string
	 */
	static protected $_includepath;

	/**
	 * The path to the application relative from url server root
	 * 
	 * @var string
	 */
	static protected $_apppath;

	/**
	 * Stripped version of the $_apppath
	 *
	 * @see $_apppath
	 *
	 * @var string
	 */
	static protected $_stripped_apppath;

	/**
	 * The i18n object
	 *
	 * @Class \caspar\core\I18n
	 */
	static protected $_i18n;

	/**
	 * The request object
	 *
	 * @var Request
	 */
	static protected $_request;

	/**
	 * The response object
	 * 
	 * @var Response
	 */
	static protected $_response;

	/**
	 * The Factory instance
	 *
	 * @var Factory
	 */
	static protected $_factory;

	/**
	 * Used to determine when caspar started loading
	 * 
	 * @var integer
	 */
	static protected $_loadstart;

	/**
	 * Used for timing purposes
	 *
	 * @var integer
	 */
	static protected $_loadend;

	/**
	 * List of loaded libraries
	 *
	 * @var string
	 */
	static protected $_libs = [];

	/**
	 * The routing object
	 * 
	 * @var Routing
	 */
	static protected $_routing;

	/**
	 * The action object
	 *
	 * @var Controller
	 */
	static protected $_actions;

	/**
	 * Module instances
	 *
	 * @var Module[]
	 */
	static protected $_modules;

	/**
	 * The debugger object
	 *
	 * @var Debugger
	 */
	static protected $_debugger = null;

	/**
	 * The path relative from url server root
	 *
	 * @var string
	 */
	protected static $_webroot = null;

	/**
	 * Stripped version of the $_webroot
	 *
	 * @see $_webroot
	 *
	 * @var string
	 */
	protected static $_stripped_webroot = null;

	/**
	 * Messages passed on from the previous request
	 *
	 * @var array
	 */
	static protected $_messages;
	static protected $_redirect_login;

	/**
	 * Returns the routing object
	 *
	 * @return Routing
	 */
	public static function getRouting()
	{
		return self::$_routing;
	}

	protected static function initializeRouting()
	{
		self::$_routing = new Routing(self::getCache());
		self::loadEventListeners(self::$_routing->getListeners());
	}

	protected static function loadEventListeners($event_listeners)
	{
		Logging::log('Loading event listeners');
		foreach ($event_listeners as $listener)
		{
			list($event_module, $event_identifier, $module, $method) = $listener;
			Event::listen($event_module, $event_identifier, [self::getModule($module), $method]);
		}
		Logging::log('... done (loading event listeners)');
	}

	/**
	 * Get the top level url
	 * 
	 * @return string
	 */
	public static function getBaseURL()
	{
		return self::$_configuration['core']['base_url'];
	}

	/**
	 * Get the cookie domain
	 *
	 * @return string
	 */
	public static function getDefaultCookieDomain()
	{
        return $_SERVER['SERVER_NAME'];
		return self::$_configuration['core']['cookie_domain'];
	}

	/**
	 * Get the cookie base path
	 *
	 * @return string
	 */
	public static function getDefaultCookiePath()
	{
		return self::$_configuration['core']['cookie_path'];
	}

	/**
	 * Get the subdirectory part of the url
	 *
	 * @return string
	 */
	public static function getBasePath()
	{
		return self::$_configuration['core']['base_path'];
	}

	/**
	 * Get the subdirectory part of the url
	 *
	 * @return string
	 */
	public static function getWebroot()
	{
		if (self::$_webroot === null)
		{
			self::_setWebroot();
		}
		return self::$_webroot;
	}

	/**
	 * Get the subdirectory part of the url, stripped
	 *
	 * @return string
	 */
	public static function getStrippedWebroot()
	{
		if (self::$_stripped_webroot === null)
		{
			self::$_stripped_webroot = (self::isCLI()) ? '' : rtrim(self::getWebroot(), '/');
		}
		return self::$_stripped_webroot;
	}

	/**
	 * Set the subdirectory part of the url, from the url
	 */
	protected static function _setWebroot()
	{
		self::$_webroot = defined('CSP_CLI') ? '.' : dirname($_SERVER['PHP_SELF']);
		if (stristr(PHP_OS, 'WIN'))
		{
			self::$_webroot = str_replace("\\", "/", self::$_webroot); /* Windows adds a \ to the URL which we don't want */
		}
		if (self::$_webroot[strlen(self::$_webroot) - 1] != '/')
			self::$_webroot .= '/';
	}

	/**
	 * Set that we've started loading
	 * 
	 * @param integer $when
	 */
	public static function setLoadStart($when)
	{
		self::$_loadstart = $when;
	}

	/**
	 * Manually ping the loader
	 */
	public static function ping()
	{
		$endtime = explode(' ', microtime());
		self::$_loadend = $endtime[1] + $endtime[0];
	}

	/**
	 * Get the time from when we started loading
	 *
	 * @param integer $precision
	 * @return integer
	 */
	public static function getLoadtime($precision = 5)
	{
		self::ping();
		return round((self::$_loadend - self::$_loadstart), $precision);
	}

	protected static function setupI18n()
	{
		if (self::isCLI())
			return null;

		$language = self::$_configuration['core']['language'];

		Logging::log('Loading i18n strings');

		if (self::$_debug_mode || !self::$_i18n = self::$_cache->get("i18n_{$language}")) {
			Logging::log("Loading strings from file ({$language})");
			self::$_i18n = new I18n($language);
			self::$_i18n->initialize();
			self::$_cache->set("i18n_{$language}", self::$_i18n);
		} else {
			Logging::log('Using cached i18n strings');
		}
		Logging::log('...done');
	}

	protected static function initializeUser()
	{
		Logging::log('Loading user');
		try {
			Logging::log('no');
			Logging::log('sets up user object');
			$event = Event::createNew('core', 'pre_login');
			$event->trigger();

            if ($event->isProcessed())
                self::loadUser($event->getReturnValue(), true);
            elseif (!self::isCLI())
                self::loadUser(null, true);
            else
                self::$_user = new User();

			Event::createNew('core', 'post_login', self::getUser())->trigger();

			Logging::log('loaded');
		} catch (Exception $e) {
			Logging::log("Something happened while setting up user: " . $e->getMessage(), 'main', Logging::LEVEL_WARNING);
			if (!self::isCLI() && (self::getRouting()->getCurrentRouteModule() != 'main' || self::getRouting()->getCurrentRouteAction() != 'register1' && self::getRouting()->getCurrentRouteAction() != 'register2' && self::getRouting()->getCurrentRouteAction() != 'activate' && self::getRouting()->getCurrentRouteAction() != 'reset_password' && self::getRouting()->getCurrentRouteAction() != 'captcha' && self::getRouting()->getCurrentRouteAction() != 'login' && self::getRouting()->getCurrentRouteAction() != 'getBackdropPartial' && self::getRouting()->getCurrentRouteAction() != 'serve' && self::getRouting()->getCurrentRouteAction() != 'doLogin'))
				self::$_redirect_login = true;
			else {
				$classname = self::$_configuration['core']['user_classname'];
				self::$_user = new $classname();
			}
		}
		Logging::log('...done');
	}

	/**
	 * Returns the factory object
	 *
	 * @return Factory
	 */
	public static function factory()
	{
		if (!self::$_factory instanceof Factory)
			self::$_factory = new Factory();

		return self::$_factory;
	}

	/**
	 * Returns the request object
	 * 
	 * @return Request
	 */
	public static function getRequest()
	{
		if (!self::$_request instanceof Request)
			self::$_request = new Request();

		return self::$_request;
	}

    public static function setResponse($response)
    {
        self::$_response = $response;
    }

	/**
	 * Returns the response object
	 *
	 * @return Response
	 */
	public static function getResponse()
	{
		if (!is_object(self::$_response)) {
			if (is_array(self::$_configuration) && array_key_exists('core', self::$_configuration)) {
				$classname = self::$_configuration['core']['response_classname'];
				self::$_response = new $classname(self::$_configuration['core']['javascripts'], self::$_configuration['core']['stylesheets'], self::$_configuration['core']['base_url']);
			} else {
				return new Response();
			}
		}
		return self::$_response;
	}

	/**
	 * Get the i18n object
	 *
	 * @return I18n
	 */
	public static function getI18n()
	{
		if (!self::$_i18n instanceof I18n) {
            self::setupI18n();
        }

		return self::$_i18n;
	}

	/**
	 * Load the user object into the user property
	 *
	 * @return User
	 */
	public static function loadUser($user = null)
	{
        $classname = self::$_configuration['core']['user_classname'];
        self::$_user = ($user === null) ? $classname::identify(self::getRequest(), self::getCurrentAction(), true) : $user;
        if (self::$_user->isAuthenticated()) {
            Event::createNew('core', 'post_loaduser', self::$_user)->trigger();
        }

		return self::$_user;
	}

	/**
	 * Returns the user object
	 *
	 * @return User
	 */
	public static function getUser()
	{
		return self::$_user;
	}

	/**
	 * Set the current user
	 * 
	 * @param User $user
	 */
	public static function setUser($user)
	{
		self::$_user = $user;
	}

	/**
	 * Log out the current user (does not work when auth method is set to http)
	 */
	public static function logout()
	{
		Event::createNew('caspar', 'caspar\pre_logout')->trigger();
        self::getResponse()->deleteCookie('username');
        self::getResponse()->deleteCookie('session_token');
		self::getResponse()->deleteCookie(CASPAR_SESSION_NAME);
		session_regenerate_id(true);
		Event::createNew('caspar', 'caspar\post_logout')->trigger();
	}

	/**
	 * Set a message to be retrieved in the next request
	 *
	 * @param string $message The message
	 */
	public static function setMessage($key, $message)
	{
		if (!array_key_exists('caspar_message', $_SESSION)) {
			$_SESSION['caspar_message'] = [];
		}
		$_SESSION['caspar_message'][$key] = $message;
	}

	protected static function _setupMessages()
	{
		if (self::$_messages === null) {
			self::$_messages = [];
			if (array_key_exists('caspar_message', $_SESSION)) {
				self::$_messages = $_SESSION['caspar_message'];
				unset($_SESSION['caspar_message']);
			}
		}
	}

	/**
	 * Whether or not there is a message in the next request
	 * 
	 * @return boolean
	 */
	public static function hasMessage($key)
	{
		self::_setupMessages();
		return array_key_exists($key, self::$_messages);
	}

	/**
	 * Retrieve a message passed on from the previous request
	 *
	 * @param string $key A message identifier
	 *
	 * @return string
	 */
	public static function getMessage($key)
	{
		return (self::hasMessage($key)) ? self::$_messages[$key] : null;
	}

	/**
	 * Clear the message
	 */
	public static function clearMessage($key)
	{
		if (self::hasMessage($key))
			unset(self::$_messages[$key]);
	}

	/**
	 * Retrieve the message and clear it
	 *
	 * @return string
	 */
	public static function getMessageAndClear($key)
	{
		if ($message = self::getMessage($key)) {
			self::clearMessage($key);
			return $message;
		}
		return null;
	}

	public static function generateCSRFtoken()
	{
		if (!array_key_exists('csrf_token', $_SESSION) || $_SESSION['csrf_token'] == '')
			$_SESSION['csrf_token'] = str_replace('.', '_', uniqid(rand(), TRUE));

		return $_SESSION['csrf_token'];
	}

	public static function checkCSRFtoken($handle_response = false)
	{
		$token = self::generateCSRFtoken();
		if ($token == self::getRequest()->getParameter('csrf_token'))
			return true;

		$message = self::getI18n()->__('An authentication error occured. Please reload your page and try again');
		throw new CSRFFailureException($message);
	}

	/**
	 * @return Module[]
	 */
	public static function getModules()
	{
		return self::$_modules;
	}

	/**
	 * @param string $module_name
	 * @return Module
	 */
	public static function getModule($module_name)
	{
		return self::$_modules[$module_name];
	}

	/**
	 * Loads a function library
	 *
	 * @param string $lib_name The name of the library
	 */
	public static function loadLibrary($lib_name)
	{
		if (mb_strpos($lib_name, '/') !== false)
			list ($module, $lib_name) = explode('/', $lib_name);

		// Skip the library if it already exists
		if (!array_key_exists($lib_name, self::$_libs)) {
			$lib_file_name = "{$lib_name}.inc.php";

			if (isset($module) && file_exists(CASPAR_MODULES_PATH . $module . DS . 'lib' . DS . $lib_file_name)) {
				require CASPAR_MODULES_PATH . $module . DS . 'lib' . DS . $lib_file_name;
				self::$_libs[$lib_name] = CASPAR_MODULES_PATH . $module . DS . 'lib' . DS . $lib_file_name;
			} elseif (file_exists(CASPAR_MODULES_PATH . self::getRouting()->getCurrentRouteModule() . DS . 'lib' . DS . $lib_file_name)) {
				// Include the library from the current module if it exists
				require CASPAR_MODULES_PATH . self::getRouting()->getCurrentRouteModule() . DS . 'lib' . DS . $lib_file_name;
				self::$_libs[$lib_name] = CASPAR_MODULES_PATH . self::getRouting()->getCurrentRouteModule() . DS . 'lib' . DS . $lib_file_name;
			} elseif (file_exists(CASPAR_APPLICATION_PATH . 'libs' . DS . $lib_file_name)) {
				// Include the library from the global library directory if it exists
				require CASPAR_APPLICATION_PATH . 'libs' . DS . $lib_file_name;
				self::$_libs[$lib_name] = CASPAR_LIB_PATH . DS . $lib_file_name;
			} elseif (file_exists(CASPAR_LIB_PATH . DS . $lib_file_name)) {
				// Include the library from the global library directory if it exists
				require CASPAR_LIB_PATH . DS . $lib_file_name;
				self::$_libs[$lib_name] = CASPAR_LIB_PATH . DS . $lib_file_name;
			} else {
				// Throw an \Exception if the library can't be found in any of
				// the above directories
				Logging::log("The \"{$lib_name}\" library does not exist in either " . CASPAR_MODULES_PATH . self::getRouting()->getCurrentRouteModule() . DS . 'lib' . DS . ' or ' . CASPAR_CORE_PATH . 'lib' . DS, 'core', Logging::LEVEL_FATAL);
				throw new LibraryNotFoundException("The \"{$lib_name}\" library does not exist in either " . CASPAR_MODULES_PATH . self::getRouting()->getCurrentRouteModule() . DS . 'lib' . DS . ' or ' . CASPAR_CORE_PATH . 'lib' . DS);
			}
		}
	}

	public static function visitPartial($template_name, $time)
	{
		if (!self::$_debug_mode)
			return;
		if (!array_key_exists($template_name, self::$_partials_visited)) {
			self::$_partials_visited[$template_name] = ['time' => $time, 'count' => 1];
		} else {
			self::$_partials_visited[$template_name]['count']++;
			self::$_partials_visited[$template_name]['time'] += $time;
		}
	}

	public static function getVisitedPartials()
	{
		return self::$_partials_visited;
	}

    /**
     * Performs an action
     *
     * @param Controller $actionObject
     * @param string $module Name of the action
     * @param string $method Name of the action method to run
     * @return bool
     * @throws ActionNotFoundException
     * @throws CSRFFailureException
     * @throws LibraryNotFoundException
     * @throws TemplateNotFoundException
     */
	public static function performAction(Controller $actionObject, $module, $method)
	{
		// Set content variable
		$content = null;

		// Set the template to be used when rendering the html (or other) output
		$template_path = CASPAR_MODULES_PATH . $module . DS . 'templates' . DS;

		// Construct the action class and method name, including any pre- action(s)
		$actionClassName = get_class($actionObject);
		$actionToRunName = 'run' . ucfirst($method);
		$preActionToRunName = 'pre' . ucfirst($method);

		// Set up the response object, responsible for controlling any output
		self::getResponse()->setPage(self::getRouting()->getCurrentRouteName());
		self::getResponse()->setTemplate(mb_strtolower($method) . '.' . self::getRequest()->getRequestedFormat() . '.php');
		self::getResponse()->setupResponseContentType(self::getRequest()->getRequestedFormat());

		// Run the specified action method set if it exists
		if (method_exists($actionObject, $actionToRunName)) {
			// Turning on output buffering
			ob_start('mb_output_handler');
			ob_implicit_flush(0);

			if (self::getRouting()->isCurrentRouteCSRFenabled())
				self::checkCSRFtoken(true);

			if (self::$_debug_mode) {
				$time = explode(' ', microtime());
				$pretime = $time[1] + $time[0];
			}
			if ($content === null) {
				Logging::log('Running main pre-execute action');
				// Running any overridden preExecute() method defined for that module
				// or the default empty one provided by Actions
				if ($pre_action_retval = $actionObject->preExecute(self::getRequest(), $method)) {
					$content = ob_get_clean();
					Logging::log('preexecute method returned something, skipping further action');
					if (self::$_debug_mode)
						$visited_templatename = "{$actionClassName}::preExecute()";
				}
			}

			if ($content === null) {
				$action_retval = null;
				if (self::getResponse()->getHttpStatus() == 200) {
					// Checking for and running action-specific preExecute() function if
					// it exists
					if (method_exists($actionObject, $preActionToRunName)) {
						Logging::log('Running custom pre-execute action');
						$actionObject->$preActionToRunName(self::getRequest(), $method);
					}

					// Running main route action
					Logging::log('Running route action ' . $actionToRunName . '()');
					if (self::$_debug_mode) {
						$time = explode(' ', microtime());
						$action_pretime = $time[1] + $time[0];
					}
					$action_retval = $actionObject->$actionToRunName(self::getRequest());
					if (self::$_debug_mode) {
						$time = explode(' ', microtime());
						$action_posttime = $time[1] + $time[0];
						self::visitPartial("{$actionClassName}::{$actionToRunName}", $action_posttime - $action_pretime);
					}
				}
				if (self::getResponse()->getHttpStatus() == 200 && $action_retval) {
					// If the action returns *any* output, we're done, and collect the
					// output to a variable to be outputted in context later
					$content = ob_get_clean();
					Logging::log('...done');
				} elseif (!$action_retval) {
					// If the action doesn't return any output (which it usually doesn't)
					// we continue on to rendering the template file for that specific action
					Logging::log('...done');
					Logging::log('Displaying template');

					// Check to see if we have a translated version of the template
					if (!self::getI18n() instanceof I18n || ($templateName = self::getI18n()->hasTranslatedTemplate(self::getResponse()->getTemplate())) === false) {
						// Check to see if the template has been changed, and whether it's in a
						// different module, specified by "module/templatename"
						if (mb_strpos(self::getResponse()->getTemplate(), '/')) {
							$newPath = explode('/', self::getResponse()->getTemplate());
							$templateName = CASPAR_MODULES_PATH . $newPath[0] . DS . 'templates' . DS . $newPath[1] . '.' . self::getRequest()->getRequestedFormat() . '.php';
						} else {
							$templateName = $template_path . self::getResponse()->getTemplate();
						}
					}

					// Check to see if the template exists and throw an \Exception otherwise
					if (!file_exists($templateName)) {
						Logging::log('The template file for the ' . $method . ' action ("' . self::getResponse()->getTemplate() . '") does not exist', 'core', Logging::LEVEL_FATAL);
						throw new TemplateNotFoundException('The template file for the ' . $method . ' action ("' . self::getResponse()->getTemplate() . '") does not exist');
					}

					self::loadLibrary('common');
					// Present template for current action
					Components::presentTemplate($templateName, $actionObject->getParameterHolder());
					$content = ob_get_clean();
					Logging::log('...completed');
				}
			} elseif (self::$_debug_mode) {
				$time = explode(' ', microtime());
				$posttime = $time[1] + $time[0];
				self::visitPartial($visited_templatename, $posttime - $pretime);
			}

			if (!isset($csp_response)) {
				/**
				 * @global Request The request object
				 */
				$csp_request = self::getRequest();

				/**
				 * @global User The user object
				 */
				$csp_user = self::getUser();

				/**
				 * @global Response The response object
				 */
				$csp_response = self::getResponse();

				/**
				 * @global Routing The routing object
				 */
				$csp_routing = self::getRouting();

				// Load the "ui" library, since this is used a lot
				self::loadLibrary('ui');
			}

			self::loadLibrary('common');
			Logging::log('rendering content');

			if (self::isMaintenanceModeEnabled() && !mb_strstr(self::getRouting()->getCurrentRouteName(), 'configure')) {
				if (!file_exists(CASPAR_APPLICATION_PATH . 'templates/offline.inc.php')) {
					throw new TemplateNotFoundException('Can not find offline mode template');
				}
				ob_start('mb_output_handler');
				ob_implicit_flush(0);
				require CASPAR_APPLICATION_PATH . 'templates/offline.inc.php';
				$content = ob_get_clean();
			}

			// Render output in correct order
			self::getResponse()->renderHeaders();

			if (self::getResponse()->getDecoration() == Response::DECORATE_DEFAULT) {
				require \CASPAR_APPLICATION_PATH . 'templates/layout.php';
			} else {
				// Render header template if any, and store the output in a variable
				if (!self::getRequest()->isAjaxCall() && self::getResponse()->doDecorateHeader()) {
					Logging::log('decorating with header');
					if (!file_exists(self::getResponse()->getHeaderDecoration())) {
						throw new TemplateNotFoundException('Can not find header decoration: ' . self::getResponse()->getHeaderDecoration());
					}
					require self::getResponse()->getHeaderDecoration();
				}

				echo $content;

				Logging::log('...done (rendering content)');

				// Render footer template if any
				if (!self::getRequest()->isAjaxCall() && self::getResponse()->doDecorateFooter()) {
					Logging::log('decorating with footer');
					if (!file_exists(self::getResponse()->getFooterDecoration())) {
						throw new TemplateNotFoundException('Can not find footer decoration: ' . self::getResponse()->getFooterDecoration());
					}
					require self::getResponse()->getFooterDecoration();
				}

				Logging::log('...done');
			}

			if (self::isDebugMode())
				self::getI18n()->addMissingStringsToStringsFile();

			return true;
		}
		else {
			Logging::log("Cannot find the method {$actionToRunName}() in class {$actionClassName}.", 'core', Logging::LEVEL_FATAL);
			throw new ActionNotFoundException("Cannot find the method {$actionToRunName}() in class {$actionClassName}. Make sure the method exists.");
		}
	}

	/**
	 * Launches the MVC framework
	 */
	public static function go()
	{
		Logging::log('Dispatching');
		try {
            if (($route = self::getRouting()->getRouteFromUrl(self::getRequest()->getParameter('url', null, false))) /* || self::isInstallmode() */) {
				if (self::$_redirect_login) {
					Logging::log('An error occurred setting up the user object, redirecting to login', 'main', Logging::LEVEL_NOTICE);
					self::setMessage('login_message_err', self::geti18n()->__('Please log in'));
					self::getResponse()->headerRedirect(self::getRouting()->generate('login_page'), 403);
				}
                // Set up the action object
                $module = $route['module'];

	            /**
	             * Set up the action object by identifying the Controller from the action. The following actions can
	             * be resolved by the Framework:
	             *
	             *  actionName          => /controllers/Main.php::runActionName()
	             *  ::actionName        => /controllers/Main.php::runActionName()
	             *  Other::actionName   => /controllers/Other.php::runActionName()
	             *
	             **/

	            $actionClassBase = "\\application\\modules\\".$route['module'].'\\controllers\\';

	            // If a separate controller is defined within the action name
	            if (strpos($route['action'], '::')) {
		            $routing = explode('::', $route['action']);

		            $moduleController = $actionClassBase . $routing[0];
		            $moduleMethod = $routing[1];

		            if (class_exists($moduleController) && is_callable($moduleController, 'run'.ucfirst($moduleMethod))) {
			            $actionObject = new $moduleController();
		            } else {
			            throw new \Exception('The `' . $route['action'] . '` controller action is not callable');
		            }
	            } else {
		            $actionClassName = $actionClassBase . 'Main';
		            $actionObject = new $actionClassName();
		            $moduleMethod = $route['action'];
	            }

			} else {
                $actionObject = new \caspar\core\controllers\Common();
                $module = 'main';
                $moduleMethod = 'notFound';
			}

			self::$_actions = $actionObject;

            self::initializeUser();

            if (self::performAction($actionObject, $module, $moduleMethod)) {
                return true;
            }
		} catch (TemplateNotFoundException $e) {
			header("HTTP/1.0 404 Not Found", true, 404);
			throw $e;
		} catch (ActionNotFoundException $e) {
			header("HTTP/1.0 404 Not Found", true, 404);
			throw $e;
			//('Module action "' . $route['action'] . '" does not exist for module "' . $route['module'] . '"', $e);
		} catch (CSRFFailureException $e) {
			self::$_response->setHttpStatus(301);
			$message = $e->getMessage();

			if (self::getRequest()->getRequestedFormat() == 'json') {
				self::$_response->setContentType('application/json');
				$message = json_encode(['message' => $message]);
			}

			self::$_response->renderHeaders();
			echo $message;
		} catch (\Exception $e) {
			header("HTTP/1.0 500 Not Found", true, 404);
			throw $e;
		}
	}

    /**
     * Returns the current action object
     *
     * @return Controller
     */
    public static function getCurrentAction()
    {
        return self::$_actions;
    }

	public static function isCLI()
	{
		return (PHP_SAPI == 'cli');
	}

	public static function getCurrentCLIusername()
	{
		$processUser = posix_getpwuid(posix_geteuid());
		return $processUser['name'];
	}

	public static function setDebugMode($mode = true)
	{
		self::$_debug_mode = $mode;
	}

	public static function isDebugMode()
	{
		return self::$_debug_mode;
	}

	protected static function cliError($title, $exception)
	{
		$trace_elements = null;
		if ($exception instanceof \Exception || $exception instanceof \Error) {
			if ($exception instanceof ActionNotFoundException) {
				CliCommand::cli_echo("Could not find the specified action\n", 'white', 'bold');
			} elseif ($exception instanceof TemplateNotFoundException) {
				CliCommand::cli_echo("Could not find the template file for the specified action\n", 'white', 'bold');
			} elseif ($exception instanceof \b2db\Exception) {
				CliCommand::cli_echo("An exception was thrown in the B2DB framework\n", 'white', 'bold');
			} else {
				CliCommand::cli_echo("An unhandled exception occurred:\n", 'white', 'bold');
			}
			echo CliCommand::cli_echo($exception->getMessage(), 'red', 'bold') . "\n";
			echo "\n";
			CliCommand::cli_echo('Stack trace') . ":\n";
			$trace_elements = $exception->getTrace();
		} else {
			if ($exception['code'] == 8) {
				CliCommand::cli_echo('The following notice has stopped further execution:', 'white', 'bold');
			} else {
				CliCommand::cli_echo('The following error occured:', 'white', 'bold');
			}
			echo "\n";
			echo "\n";
			CliCommand::cli_echo($title, 'red', 'bold');
			echo "\n";
			CliCommand::cli_echo("occured in\n");
			CliCommand::cli_echo($exception['file'] . ', line ' . $exception['line'], 'blue', 'bold');
			echo "\n";
			echo "\n";
			CliCommand::cli_echo("Backtrace:\n", 'white', 'bold');
			$trace_elements = debug_backtrace();
		}
		foreach ($trace_elements as $trace_element) {
			if (array_key_exists('class', $trace_element)) {
				if ($trace_element['class'] == 'caspar\\core\\Caspar' && in_array($trace_element['function'], ['errorHandler', 'exceptionHandler']))
					continue;
				CliCommand::cli_echo($trace_element['class'] . $trace_element['type'] . $trace_element['function'] . '()');
			} elseif (array_key_exists('function', $trace_element)) {
				CliCommand::cli_echo($trace_element['function'] . '()');
			}
			else {
				CliCommand::cli_echo('unknown function');
			}
			echo "\n";
			if (array_key_exists('file', $trace_element)) {
				CliCommand::cli_echo($trace_element['file'] . ', line ' . $trace_element['line'], 'blue', 'bold');
			} else {
				CliCommand::cli_echo('unknown file', 'red', 'bold');
			}
			echo "\n";
		}
		if (class_exists('\\b2db\\Core')) {
			echo "\n";
			CliCommand::cli_echo("SQL queries:\n", 'white', 'bold');
			try {
				$cc = 1;
				foreach (\b2db\Core::getSQLHits() as $details) {
					CliCommand::cli_echo("(" . $cc++ . ") [");
					$str = ($details['time'] >= 1) ? round($details['time'], 2) . ' seconds' : round($details['time'] * 1000, 1) . 'ms';
					CliCommand::cli_echo($str);
					CliCommand::cli_echo("] from ");
					CliCommand::cli_echo($details['filename'], 'blue');
					CliCommand::cli_echo(", line ");
					CliCommand::cli_echo($details['line'], 'white', 'bold');
					CliCommand::cli_echo(":\n");
					CliCommand::cli_echo("{$details['sql']}\n");
				}
				echo "\n";
			} catch (Exception $e) {
				CliCommand::cli_echo("Could not generate query list (there may be no database connection)", "red", "bold");
			}
		}
		echo "\n";
	}

	/**
	 * Displays a nicely formatted exception message
	 *
	 * @param string $title
	 * @param \Exception $exception
	 */
	public static function exceptionHandler($exception)
	{
        error_log($exception->getMessage());
        error_log($exception->getTraceAsString());
		if (self::isCLI()) {
			self::cliError($exception->getMessage(), $exception);
		} else {
			if (self::getRequest() instanceof Request && self::getRequest()->isAjaxCall()) {
				self::getResponse()->ajaxResponseText(404, $exception->getMessage());
			}

//			var_dump($exception);
			self::getResponse()->cleanBuffer();

			require CASPAR_PATH . 'templates' . DS . 'error.php';
		}
		die();
	}

	public static function errorHandler($code, $error, $file, $line)
	{
        error_log($code . ': ' . $error);
        error_log($file . ':' . $line);
		$details = compact('code', 'error', 'file', 'line');

		if (self::isCLI()) {
			self::cliError($error, $details);
		} else {
			if (self::getRequest() instanceof Request && self::getRequest()->isAjaxCall()) {
				self::getResponse()->ajaxResponseText(404, $error);
			}
	
			self::getResponse()->cleanBuffer();
			require CASPAR_PATH . 'templates' . DS . 'error.php';
		}
		die();
	}

	protected static function _loadEnvironmentConfiguration($environment = null)
	{
		if (!self::$_debug_mode && $configuration = self::$_cache->get(self::CACHE_KEY_SETTINGS . $environment)) {
			if (self::$_cache->getType() == Cache::TYPE_APC) {
				Logging::log('Using cached configuration');
			} else {
				Logging::log('Using file cached configuration');
			}
		} else {
			Logging::log('Configuration not cached. Retrieving configuration from file');
			$filename = \CASPAR_APPLICATION_PATH . 'configuration' . \DS . "caspar{$environment}.yml";
			$configuration = (file_exists($filename)) ? \Spyc::YAMLLoad($filename, true) : [];
			self::$_cache->set(self::CACHE_KEY_SETTINGS . $environment, $configuration);
			Logging::log('Configuration loaded');
		}
		return $configuration;
	}

	public static function loadConfiguration()
	{
		Logging::log('Loading Caspar configuration');
		self::$_ver_mj = 1;
		self::$_ver_mn = 0;
		self::$_ver_rev = '0-dev';
		self::$_ver_name = 'Ninja';

		$configuration = self::_loadEnvironmentConfiguration();
		$configuration = array_replace_recursive($configuration, self::_loadEnvironmentConfiguration('_' . self::$_environment));

		self::$_configuration = $configuration;

		if (self::getConfiguration('core', 'debug') === true) {
			self::$_debug_mode = true;
			self::getResponse()->addStylesheet('/css/debugger.css');
			self::getResponse()->addStylesheet('/css/cspdebugger.css');
			self::$_debugger = new Debugger();
		} else {
			self::$_debug_mode = false;
		}
		unset($configuration['core']);
		if (array_key_exists('services', $configuration)) {
			self::$_serviceconfigurations = $configuration['services'];
		} else {
			self::$_serviceconfigurations = [];
		}
	}

	protected static function initializeService($service, $configuration)
	{

	}

	protected static function initializeServices()
	{
		if (!is_array(self::$_serviceconfigurations)) {
			self::$_serviceconfigurations = [];
		} else {
			foreach (self::$_serviceconfigurations as $service => $configuration) {
			    try {
                    if (array_key_exists('auto_initialize', $configuration) && $configuration['auto_initialize'] == true) {
                        if (array_key_exists('callback', $configuration)) {
                            $callback = $configuration['callback'];
                            $arguments = array_key_exists('arguments', $configuration) ? $configuration['arguments'] : [];
                            if (!is_callable($callback)) {
                                throw new \Exception('Cannot auto-initialize service ' . $service . ', invalid auto-initialize method.');
                            }
                            call_user_func_array($callback, $arguments);
                        }
                    }
                } catch (\Exception $e) {

			        throw new \Exception('There was an error trying to initialize service ' . $service . ', defined in caspar.yml: ' . $e->getMessage());

                }
			}
		}
	}

	public static function getServiceConfiguration($service)
	{
		if (array_key_exists($service, self::$_serviceconfigurations)) {
			return self::$_serviceconfigurations[$service];
		}
		return false;
	}

	public static function getService($service)
	{
		if (!array_key_exists($service, self::$_services)) {
			$configuration = self::getServiceConfiguration($service);
			$classname = $configuration['classname'];
			self::$_services[$service] = new $classname(... $configuration['arguments']);
		}

		return self::$_services[$service];
	}

	public static function registerErrorHandlers()
	{
		set_exception_handler([self::class, 'exceptionHandler']);
		set_error_handler([self::class, 'errorHandler']);
		error_reporting(E_ALL | E_NOTICE | E_STRICT);
	}

	public static function bootstrap()
	{
        if (!defined('CASPAR_PATH')) {
            throw new \RuntimeException('You must define the CASPAR_PATH constant so we can find the files we need');
        }

        if (!defined('CASPAR_APPLICATION_PATH')) {
            throw new \RuntimeException('You must define the CASPAR_APPLICATION_PATH constant so we can find the application files');
        }

        date_default_timezone_set('UTC');
        mb_internal_encoding("UTF-8");
        mb_language('uni');
        mb_http_output("UTF-8");

        defined('CASPAR_CORE_PATH') || define('CASPAR_CORE_PATH', CASPAR_PATH . 'core' . DS);
        defined('CASPAR_LIB_PATH') || define('CASPAR_LIB_PATH', CASPAR_PATH . 'libs' . DS);
        defined('CASPAR_MODULES_PATH') || define('CASPAR_MODULES_PATH', CASPAR_APPLICATION_PATH . 'modules' . DS);
        defined('CASPAR_ENTITIES_PATH') || define('CASPAR_ENTITIES_PATH', CASPAR_APPLICATION_PATH . 'entities' . DS);
        defined('CASPAR_SESSION_NAME') || define('CASPAR_SESSION_NAME', 'CASPAR_SESSION');

        if (!isset($GLOBALS['argc']) && !ini_get('session.auto_start')) {
            session_name(CASPAR_SESSION_NAME);
            session_start();
        }
	}

	protected static function verifyCache()
	{

		if (!self::$_cache instanceof Cache) {
			self::$_cache = new Cache(Cache::TYPE_DUMMY);
		}

		Logging::log(self::$_cache->getCacheTypeDescription() . ' enabled');

	}

	/**
	 * @return Cache
	 */
	public static function getCache()
	{
		return self::$_cache;
	}

	protected static function loadModules()
	{
		self::$_modules = [];

		if (file_exists(CASPAR_MODULES_PATH)) {
			$iterator = new \DirectoryIterator(CASPAR_MODULES_PATH);
			foreach ($iterator as $fileinfo)
			{
				if ($fileinfo->isDir())
				{
					$module_name = $fileinfo->getBasename();
					$classname = '\\application\\modules\\' . $module_name . ucfirst($module_name);
					if (class_exists($classname)) {
						$module = new $classname($module_name);
					} else {
						$module = new Module($module_name);
					}

					self::$_modules[$module_name] = $module;
				}
			}
		}
	}

	public static function initialize()
	{
	    self::bootstrap();

		// The time the script was loaded
		$starttime = explode(' ', microtime());
		define('NOW', $starttime[1]);

		// Set the start time
		self::setLoadStart($starttime[1] + $starttime[0]);

		// Start loading Caspar
		Logging::log('Initializing Caspar framework');
		Logging::log('PHP_SAPI says "' . \PHP_SAPI . '"');
		Logging::log('PHP_VERSION_ID says "' . \PHP_VERSION_ID . '"');
		Logging::log('PHP_VERSION says "' . \PHP_VERSION . '"');

		self::verifyCache();

        $bootstrap_file = CASPAR_APPLICATION_PATH . 'bootstrap.inc.php';
        if (file_exists($bootstrap_file)) {
            require $bootstrap_file;
        }

		self::loadConfiguration();
		self::initializeServices();

		self::loadModules();
		self::initializeRouting();

		Logging::log('Caspar framework loaded');
		$event = Event::createNew('caspar/core', 'post_initialize')->trigger();

		if (!self::isCLI())
			self::go();
	}

	public static function setEnvironment($environment)
	{
		self::$_environment = $environment;
	}

	public static function getEnvironment()
	{
		return self::$_environment;
	}

	public static function setCacheStrategy(Cache $cache)
	{
		self::$_cache = $cache;
	}

	public static function isMaintenanceModeEnabled()
	{
		return false;
	}

	public static function getSalt()
	{
		return self::$_configuration['core']['salt'];
	}

    public static function getConfiguration($section, $key = null)
    {
        if (!array_key_exists($section, self::$_configuration)) return ($key !== null) ? null : [];

        return (array_key_exists($key, self::$_configuration[$section])) ? self::$_configuration[$section][$key] : null;
    }

	/**
	 * Return the debugger instance
	 *
	 * @return Debugger
	 */
	public static function getDebugger()
	{
		return self::$_debugger;
	}

}


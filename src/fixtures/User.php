<?php

    namespace application\entities;

    use application\entities\tables\Users;
    use application\entities\tables\UserTokens;
    use caspar\core\Actions;
    use caspar\core\Caspar;
    use caspar\core\Logging;
    use caspar\core\Request;

    /**
     * User class
     *
     * @package caspar
     * @subpackage core
     *
     * @Table(name="\application\entities\tables\Users")
     */
    class User extends \b2db\Saveable
    {

        /**
         * Unique identifier
         *
         * @Id
         * @Column(type="integer", auto_increment=true, length=10)
         * @var integer
         */
        protected $_id;

        /**
         * Timestamp of when the user was created
         *
         * @Column(type="integer", length=10)
         * @var integer
         */
        protected $_created_at;

        /**
         * Unique username (login name)
         *
         * @Column(type="string", length=200)
         * @var string
         */
        protected $_username = '';

        /**
         * Whether or not the user has authenticated
         *
         * @var boolean
         */
        protected $authenticated = false;

        /**
         * Hashed password
         *
         * @Column(type="string", length=200)
         * @var string
         */
        protected $_password = '';

        /**
         * User real name
         *
         * @Column(type="string", length=200)
         * @var string
         */
        protected $_realname = '';

        /**
         * User email
         *
         * @Column(type="string", length=250)
         * @var string
         */
        protected $_email = '';

        /**
         * Timestamp of when the user was last seen
         *
         * @Column(type="integer", length=10)
         * @var integer
         */
        protected $_lastseen = 0;

        /**
         * Whether the user is enabled
         *
         * @Column(type="boolean", default=true)
         * @var boolean
         */
        protected $_enabled = true;

        /**
         * Whether the user is an admin
         *
         * @Column(type="boolean", default=false)
         * @var boolean
         */
        protected $_isadmin = false;

        /**
         * Whether the user is a superadmin
         *
         * @Column(type="boolean", default=false)
         * @var boolean
         */
        protected $_is_superadmin = false;

        /**
         * Whether the user is activated
         *
         * @Column(type="boolean", default=false)
         * @var boolean
         */
        protected $_activated = true;

	    /**
	     * List of user's session tokens
	     *
	     * @var \application\entities\UserSession[]
	     * @Relates(class="\application\entities\UserSession", collection=true, foreign_column="user_id", orderby="created_at")
	     */
	    protected $_user_sessions = null;

	    protected static function autoVerifyToken($username, $token)
	    {
		    $user = Users::getTable()->getByUsername($username);

		    if (!$user instanceof User) {
			    Caspar::logout();
			    return;
		    }

		    if (!$user->verifyUserSession($token)) {
			    $user = null;
		    }

		    return $user;
	    }

	    /**
	     * @param Request $request
	     *
	     * @return null|User
	     */
	    public static function doExplicitLogin(Request $request)
	    {
		    $username = $request['username'];
		    $password = $request['password'];

		    $user = Users::getTable()->getByUsername($username);

		    if (!$user instanceof User) {
			    Caspar::logout();
			    return;
		    }

		    if (!$user->hasPassword($password)) {
			    $user = null;
		    }

		    return $user;
	    }

	    /**
	     * @param User $user
	     * @param UserSession $token
	     * @param bool $session_only
	     * @return mixed|void
	     */
	    public static function persistTokenSession(User $user, UserSession $token, $session_only)
	    {
		    if ($session_only) {
			    Caspar::getResponse()->setSessionCookie('username', $user->getUsername());
			    Caspar::getResponse()->setSessionCookie('session_token', $token->getToken());
		    } else {
			    Caspar::getResponse()->setCookie('username', $user->getUsername());
			    Caspar::getResponse()->setCookie('session_token', $token->getToken());
		    }
	    }

	    public static function identify(Request $request, Actions $action, $auto = false)
	    {
		    if ($auto) {
			    $user = static::autoVerifyToken($request->getCookie('username'), $request->getCookie('session_token'));
		    } else {
			    // If we don't have login details, try logging in with provided parameters
			    $user = static::doExplicitLogin($request);
		    }

		    if (!$user instanceof User) {
			    $user = new User();
		    }

		    return $user;
	    }

	    /**
	     * Returns an array of user sessions
	     *
	     * @return UserSession[]
	     */
	    public function getUserSessions()
	    {
		    $this->_b2dbLazyload('_user_sessions');
		    return $this->_user_sessions;
	    }

	    /**
	     * @return UserSession
	     *
	     * @throws \Exception
	     */
	    public function createUserSession()
	    {
		    $userSession = new UserSession();
		    $userSession->setUser($this);
		    $userSession->save();

		    $this->_user_sessions = null;

		    return $userSession;
	    }

	    public function verifyUserSession($token)
	    {
		    $userSessions = $this->getUserSessions();
		    Logging::log('Cycling user sessions for given user. Count: '.count($userSessions), 'auth', Logging::LEVEL_INFO);

		    foreach ($userSessions as $userSession)
		    {
			    if ($userSession->getExpiresAt() < time())
			    {
				    $userSession->delete();
				    continue;
			    }

			    if ($userSession->getToken() == $token)
			    {
				    Logging::log('Verified user session', 'auth', Logging::LEVEL_INFO);
				    return true;
			    }
		    }

		    Logging::log('Could not verify user session', 'auth', Logging::LEVEL_INFO);
		    return false;
	    }

	    public function logout()
	    {
	    }

	    /**
         * Take a raw password and convert it to the hashed format
         *
         * @param string $password
         * @param null $salt
         *
         * @return hashed password
         */
        public static function hashPassword($password)
        {
            return password_hash($password, PASSWORD_DEFAULT);
        }

        /**
         * Retrieve the users real name
         *
         * @return string
         */
        public function getName()
        {
            return ($this->_realname) ? $this->_realname : $this->_username;
        }

        /**
         * Retrieve the users id
         *
         * @return integer
         */
        public function getID()
        {
            return $this->_id;
        }

        /**
         * Retrieve this users realname and username combined
         *
         * @return string "Real Name (username)"
         */
        public function getNameWithUsername()
        {
            return ($this->_realname) ? $this->_realname . ' (' . $this->_username . ')' : $this->_username;
        }

        public function __toString()
        {
            return $this->getNameWithUsername();
        }

        protected function _preSave($is_new = false)
        {
            if ($is_new) {
                $this->_created_at = NOW;
            }
        }

        /**
         * Set users "last seen" property to NOW
         */
        public function updateLastSeen()
        {
            $this->_lastseen = NOW;
        }

        /**
         * Return timestamp for when this user was last online
         *
         * @return integer
         */
        public function getLastSeen()
        {
            return $this->_lastseen;
        }

        public function getCreatedAt()
        {
            return $this->_created_at;
        }

        public function setCreatedAt($created_at)
        {
            $this->_created_at = $created_at;
        }

        /**
         * Checks whether or not the user is logged in
         *
         * @return boolean
         */
        public function isAuthenticated()
        {
            return (bool) $this->_id;
        }

        /**
         * Alias for changePassword
         *
         * @param string $newpassword
         *
         * @return string
         */
        public function setPassword($newpassword)
        {
            $this->_password = self::hashPassword($newpassword);

            return $this->_password;
        }

        /**
         * Whether this user is enabled or not
         *
         * @return boolean
         */
        public function isEnabled()
        {
            return $this->_enabled;
        }

        /**
         * Set whether this user is activated or not
         *
         * @param boolean $val[optional]
         */
        public function setActivated($val = true)
        {
            $this->_activated = (boolean) $val;
        }

        /**
         * Whether this user is activated or not
         *
         * @return boolean
         */
        public function isActivated()
        {
            return $this->_activated;
        }

        /**
         * Set the username
         *
         * @param string $username
         */
        public function setUsername($username)
        {
            $this->_username = $username;
        }

        /**
         * Return this users' username
         *
         * @return string
         */
        public function getUsername()
        {
            return $this->_username;
        }

        /**
         * Returns a hash of the user password
         *
         * @return string
         */
        public function getPassword()
        {
            return $this->_password;
        }

	    /**
	     * Return whether or not the users password is this
	     *
	     * @param string $password Unhashed password
	     *
	     * @return boolean
	     */
	    public function hasPassword($password)
	    {
		    return password_verify($password, $this->_password);
	    }

        /**
         * Returns the real name (full name) of the user
         *
         * @return string
         */
        public function getRealname()
        {
            return $this->_realname;
        }

        /**
         * Returns the email of the user
         *
         * @return string
         */
        public function getEmail()
        {
            return $this->_email;
        }

        /**
         * Returns whether this has has a registered email addresss or not
         *
         * @return bool
         */
        public function hasEmail()
        {
            return (bool) $this->_email;
        }

        /**
         * Set the users email address
         *
         * @param string $email A valid email address
         */
        public function setEmail($email)
        {
            $this->_email = $email;
        }

        /**
         * Set the users realname
         *
         * @param string $realname
         */
        public function setRealname($realname)
        {
            $this->_realname = $realname;
        }

        /**
         * Set whether this user is enabled or not
         *
         * @param boolean $val[optional]
         */
        public function setEnabled($val = true)
        {
            $this->_enabled = $val;
        }

        /**
         * Set whether this user is validated or not
         *
         * @param boolean $val[optional]
         */
        public function setValidated($val = true)
        {
            $this->_activated = $val;
        }

        public function setIsAdmin($isadmin = true)
        {
            $this->_isadmin = $isadmin;
        }

        public function isAdmin()
        {
            return (bool) $this->_isadmin;
        }

        public function setIsSuperAdmin($is_superadmin = true)
        {
            $this->_is_superadmin = $is_superadmin;
        }

        public function isSuperAdmin()
        {
            return (bool) $this->_is_superadmin;
        }

        public function getOrCreateToken()
        {
	        $token = UserTokens::getTable()->getValidByUserId($this->getID());

	        if (!$token instanceof UserToken) {
	        	$token = new UserToken();
	        	$token->setUser($this);
	        	$token->save();
	        }

	        return $token;
        }

    }


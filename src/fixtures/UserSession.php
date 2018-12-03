<?php

    namespace application\entities;

    use Ramsey\Uuid\Uuid;

    /**
     * User sessions class
     *
     * @package caspar
     * @subpackage core
     *
     * @Table(name="\application\entities\tables\UserSessions")
     */
    class UserSession extends \b2db\Saveable
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
         * The name of the object
         *
         * @var string
         * @Column(type="string", length=200)
         */
        protected $_name;

        /**
         * The session token
         *
         * @var string
         * @Column(type="string", length=200)
         */
        protected $_token;

        /**
         * @var int
         * @Column(type="integer", length=10)
         */
        protected $_created_at;

        /**
         * @var int
         * @Column(type="integer", length=10)
         */
        protected $_last_used_at;

        /**
         * @var bool
         * @Column(type="boolean", default=false)
         */
        protected $_is_elevated = false;

        /**
         * @var int
         * @Column(type="integer", length=10)
         */
        protected $_expires_at;

        /**
         * Who the session is for
         *
         * @var User
         * @Column(type="integer", length=10)
         * @Relates(class="\application\entities\User")
         */
        protected $_user_id;

        protected function _preSave($is_new = false)
        {
            if ($is_new)
            {
                $this->_token = Uuid::uuid4()->toString();
                $this->_created_at = time();

                // Set session token to expire after 30 days
                $this->_expires_at = $this->_created_at + (86400 * 30);
            }
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

        public function setUser(User $user)
        {
            $this->_user_id = $user;
        }

        public function setUserId($user_id)
        {
            $this->_user_id = $user_id;
        }

        /**
         * Returns the associated user
         *
         * @return \thebuggenie\core\entities\User
         */
        public function getUser()
        {
            return $this->_b2dbLazyload('_user_id');
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->_name;
        }

        /**
         * @param string $name
         */
        public function setName($name)
        {
            $this->_name = $name;
        }

        /**
         * @return string
         */
        public function getToken()
        {
            return $this->_token;
        }

        /**
         * @param string $identifier
         */
        public function setToken($identifier)
        {
            $this->_token = $identifier;
        }

        /**
         * @return int
         */
        public function getCreatedAt()
        {
            return $this->_created_at;
        }

        /**
         * @param int $created_at
         */
        public function setCreatedAt($created_at)
        {
            $this->_created_at = $created_at;
        }

        /**
         * @return int
         */
        public function getLastUsedAt()
        {
            return $this->_last_used_at;
        }

        /**
         * @param int $last_used_at
         */
        public function setLastUsedAt($last_used_at)
        {
            $this->_last_used_at = $last_used_at;
        }

        /**
         * @return bool
         */
        public function isElevated()
        {
            return $this->_is_elevated;
        }

        /**
         * @param bool $is_elevated
         */
        public function setIsElevated($is_elevated)
        {
            $this->_is_elevated = $is_elevated;
        }

        /**
         * @return int
         */
        public function getExpiresAt()
        {
            return $this->_expires_at;
        }

        /**
         * @param int $expires_at
         */
        public function setExpiresAt($expires_at)
        {
            $this->_expires_at = $expires_at;
        }

    }

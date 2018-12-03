<?php

    namespace application\entities;

    /**
     * User token class
     *
     * @package caspar
     * @subpackage core
     *
     * @Table(name="\application\entities\tables\UserTokens")
     */
    class UserToken extends \b2db\Saveable
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
         * Timestamp of when the district completion was started
         *
         * @Column(type="integer", length=10)
         * @var integer
         */
        protected $_created_at;

        /**
         * Associated user
         *
         * @Column(type="integer", length=10)
         * @Relates(class="\application\entities\User")
         *
         * @var \application\entities\User
         */
        protected $_user_id;

        /**
         * Number of times the token is used
         *
         * @Column(type="integer", default=0)
         * @var int
         */
        protected $_times_used = 0;

        /**
         * The token id
         *
         * @Column(type="string", length=200)
         * @var string
         */
        protected $_token;

        protected function _preSave($is_new)
        {
            if ($is_new) {
                $this->_created_at = time();
                $this->_token = base64_encode($this->getUser()->getUsername() . time() . uniqid());
            }
        }

        public function getId()
        {
            return $this->_id;
        }

        public function setId($id)
        {
            $this->_id = $id;
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
	     * @return User
	     */
        public function getUser()
        {
            return $this->_b2dbLazyload('_user_id');
        }

        public function getUserId()
        {
            return (is_object($this->_user_id)) ? $this->_user_id->getId() : (int) $this->_user_id;
        }

        public function setUser($user_id)
        {
            $this->_user_id = $user_id;
        }

	    /**
	     * @return int
	     */
	    public function getTimesUsed()
	    {
		    return $this->_times_used;
	    }

	    /**
	     * @param int $times_used
	     */
	    public function setTimesUsed($times_used)
	    {
		    $this->_times_used = $times_used;
	    }

	    /**
	     * @return string
	     */
	    public function getToken()
	    {
		    return $this->_token;
	    }

	    /**
	     * @param string $token
	     */
	    public function setToken($token)
	    {
		    $this->_token = $token;
	    }

	    public function useIfValid()
	    {
	    	if ($this->_created_at >= time() - 86400) {
	    		$this->_times_used += 1;
	    		$this->save();

	    		return true;
		    } else {

	    		return false;
		    }
	    }

    }


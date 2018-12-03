<?php

    namespace application\traits;

    use application\entities\User;
    use caspar\core\Request;

    /**
     * Login-specific actions
     */
    trait LoginFunctions
    {

        /**
         * Perform login
         *
         * @param Request $request
         */
        public function runLogin(Request $request)
        {
            if ($request->isPost()) {
                try {
	                $username = trim($request->getParameter('username', ''));
	                $password = trim($request->getParameter('password', ''));
	                $persist  = (bool) $request->getParameter('rememberme', false);

	                if ($username && $password)
	                {
		                $user = User::identify($request, $this);

		                if (!$user instanceof User || !$user->isAuthenticated())
		                {
			                throw new \Exception('Unknown username and / or password');
		                }

		                Caspar::setUser($user);

		                $token = $user->createUserSession();
		                User::persistTokenSession($user, $token, $persist);
	                }
	                else
	                {
		                throw new \Exception('Please enter a username and password');
	                }
                } catch (\Exception $e) {
                    \caspar\core\Caspar::setMessage('login_error', $e->getMessage());
                }
            } elseif ($request['token'] != '') {
            	$token = UserTokens::getTable()->getByToken($request['token']);

            	if ($token instanceof UserToken) {
            		if ($token->useIfValid()) {
            			$user = $token->getUser();
            			$session_token = $user->createUserSession();
            			User::persistTokenSession($user, $session_token, false);

	                    \caspar\core\Caspar::setMessage('login_token', true);
		            } else {
	                    \caspar\core\Caspar::setMessage('login_error', "That looks like a valid link, but it is a bit old so it has expired.");
		            }
	            } else {
                    \caspar\core\Caspar::setMessage('login_error', "That doesn't look like a valid token, sorry.");
	            }
            }

            if ($request->hasParameter('redirect') && array_key_exists($request->getParameter('redirect'), $this->getRouting()->getRoutes())) {
	            $this->forward($this->getRouting()->generate($request->getParameter('redirect')));
            } else {
	            $this->forward($this->getRouting()->generate('home'));
            }

        }

        public function runLogout(Request $request)
        {
            $this->getUser()->logout();
            \caspar\core\Caspar::logout();
            $this->forward($this->getRouting()->generate('home'));
        }

    }

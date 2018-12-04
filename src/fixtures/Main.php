<?php

    namespace application\modules\main\controllers;

    use caspar\core\Request;
    use application\traits\LoginFunctions;

    /**
     * Actions for the main module
     */
    class Main extends \caspar\core\Controller
    {

        use LoginFunctions;

        /**
         * Pre execute action
         *
         * @param \caspar\core\Request $request
         * @param string $action
         *
         * @return integer|null
         */
        public function preExecute(Request $request, $action)
        {
            // Placeholder method to run any pre-action methods
            parent::preExecute($request, $action);
        }

        /**
         * Index page
         *
         * @Route(name="home", url="/")
         * @param Request $request
         */
        public function runIndex(Request $request)
        {
        }

        /**
         * Default "notfound" page
         *
         * @Route(name="notfound", url="/404")
         * @param Request $request
         */
        public function runNotFound(Request $request)
        {
            $this->getResponse()->setHttpStatus(404);
        }

    }

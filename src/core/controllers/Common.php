<?php

namespace caspar\core\controllers;

use caspar\core\Controller;
use caspar\core\Request;

/**
 * actions for the main module
 */
class Common extends Controller
{

    /**
     * About page
     *
     * @param \caspar\core\Request $request
     */
    public function runAbout(Request $request)
    {
        $this->forward403unless($this->getUser()->hasPageAccess('about'));
    }

    /**
     * 404 not found page
     *
     * @Route(name="notfound", url="/404")
     * @param \caspar\core\Request $request
     */
    public function runNotFound(Request $request)
    {
        $this->getResponse()->setHttpStatus(404);
        $message = null;
    }

    /**
     * 403 forbidden page
     *
     * @param \caspar\core\Request $request
     */
    public function runForbidden(Request $request)
    {
        $this->getResponse()->setHttpStatus(403);
        $this->getResponse()->setTemplate('main/forbidden');
    }

}

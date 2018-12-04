<?php

    namespace caspar\core;

    /**
     * Core module class
     *
     * @package caspar
     * @subpackage core
     */
    class Module
    {

        protected $name;

        public function __construct($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }

        public function initialize() {}

    }

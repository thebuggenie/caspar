<?php

    namespace caspar\core;

    /**
     * Request class, used for retrieving request information
     *
     * @author Daniel Andre Eikeland <zegenie@gmail.com>
     * @version 1.0
     * @license http://www.opensource.org/licenses/mozilla1.1.php Mozilla Public License 1.1 (MPL 1.1)
     * @package caspar
     * @subpackage mvc
     */

    /**
     * Request class, used for retrieving request information
     *
     * @package caspar
     * @subpackage mvc
     */
    class Request implements \ArrayAccess
    {

        const POST = 'post';
        const GET = 'get';
        const DELETE = 'delete';
        const PUT = 'put';
        const OPTIONS = 'options';

        protected $_request_parameters = [];
        protected $_post_parameters = [];
        protected $_get_parameters = [];
        protected $_json_body_parameters = [];
        protected $_files = [];
        protected $_cookies = [];
        protected $_query_string = null;

        protected $_has_files = false;

        protected $_is_ajax_call = false;

        /**
         * Handles an uploaded file, stores it to the correct folder, adds an entry
         * to the database and returns a TBGFile object
         *
         * @param string $thefile The request parameter the file was sent as
         *
         * @return TBGFile The TBGFile object
         */
        public function handleUpload($key, $file_name = null, $file_dir = null)
        {
            try {
                $file = $this->getUploadedFile($key);
                if ($file !== null) {
                    if ($file['error'] == UPLOAD_ERR_OK) {
                        Logging::log('No upload errors');
                        if (is_uploaded_file($file['tmp_name'])) {
                            Logging::log('Uploaded file is uploaded');
                            $files_dir = ($file_dir === null) ? Caspar::getUploadPath() : $file_dir;
                            $new_filename = ($file_name === null) ? Caspar::getUser()->getID() . '_' . NOW . '_' . basename($file['name']) : $file_name;
                            Logging::log('Moving uploaded file to ' . $new_filename);
                            if (!move_uploaded_file($file['tmp_name'], $files_dir . $new_filename)) {
                                Logging::log('Moving uploaded file failed!');
                                throw new \Exception(Caspar::getI18n()->__('An error occured when saving the file'));
                            } else {
                                Logging::log('Upload complete and ok');
                                return true;
                            }
                        } else {
                            Logging::log('Uploaded file was not uploaded correctly');
                            throw new \Exception(Caspar::getI18n()->__('The file was not uploaded correctly'));
                        }
                    } else {
                        Logging::log('Upload error: ' . $file['error']);
                        switch ($file['error']) {
                            case UPLOAD_ERR_INI_SIZE:
                            case UPLOAD_ERR_FORM_SIZE:
                                throw new \Exception(Caspar::getI18n()->__('You cannot upload files bigger than %max_size% MB', ['%max_size%' => Settings::getUploadsMaxSize()]));
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                throw new \Exception(Caspar::getI18n()->__('The upload was interrupted, please try again'));
                                break;
                            case UPLOAD_ERR_NO_FILE:
                                throw new \Exception(Caspar::getI18n()->__('No file was uploaded'));
                                break;
                            default:
                                throw new \Exception(Caspar::getI18n()->__('An unhandled error occured') . ': ' . $file['error']);
                                break;
                        }
                    }
                    Logging::log('Uploaded file could not be uploaded');
                    throw new \Exception(Caspar::getI18n()->__('The file could not be uploaded'));
                }
                Logging::log('Could not find uploaded file' . $key);
                throw new \Exception(Caspar::getI18n()->__('Could not find the uploaded file. Please make sure that it is not too big.'));
            } catch (Exception $e) {
                Logging::log('Upload exception: ' . $e->getMessage());
                throw $e;
            }
        }

        /**
         * Sanitizes a given parameter and returns it
         *
         * @param mixed $params
         *
         * @return mixed
         */
        protected function __sanitize_params($params)
        {
            if (is_array($params)) {
                foreach ($params as $key => $param) {
                    if (is_string($param)) {
                        $params[$key] = $this->__sanitize_string($param);
                    } elseif (is_array($param)) {
                        $params[$key] = $this->__sanitize_params($param);
                    }
                }
            } elseif (is_string($params)) {
                $params = $this->__sanitize_string($params);
            }
            return $params;
        }

        /**
         * Sets up the TBGRequest object and initializes and assigns the correct
         * variables
         */
        public function __construct()
        {
            foreach ($_COOKIE as $key => $value) {
                $this->_cookies[$key] = $value;
            }
            foreach ($_POST as $key => $value) {
                $this->_post_parameters[$key] = $value;
                $this->_request_parameters[$key] = $value;
            }
            foreach ($_GET as $key => $value) {
                $this->_get_parameters[$key] = $value;
                $this->_request_parameters[$key] = $value;
            }
            if ($this->getRequestedFormat() == 'json') {
                $json_body = json_decode(file_get_contents('php://input'), true);
                if (is_array($json_body)) {
                    foreach ($json_body as $key => $value) {
                        $this->_json_body_parameters[$key] = $value;
                    }
                }
            }
            foreach ($_FILES as $key => $file) {
                $this->_files[$key] = $file;
                if ($file['error'] != UPLOAD_ERR_NO_FILE) {
                    $this->_has_files = true;
                }
            }
            //var_dump($this->_request_parameters);die();
            $this->_is_ajax_call = (array_key_exists("HTTP_X_REQUESTED_WITH", $_SERVER) && mb_strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest');

            $this->_query_string = (!Caspar::isCLI()) ? $_SERVER['QUERY_STRING'] : '';
        }

        public function hasFileUploads()
        {
            return (bool)$this->_has_files;
        }

        public function getUploadedFile($key)
        {
            if (isset($this->_files[$key])) {
                return $this->_files[$key];
            }
            return null;
        }

        public function getUploadedFiles()
        {
            return $this->_files;
        }

        /**
         * Get all parameters from the request
         *
         * @return array
         */
        public function getParameters()
        {
            return array_diff_key($this->_request_parameters, ['url' => null]);
        }

        /**
         * Get a parameter from the json body
         *
         * @param string $key The parameter you want to retrieve
         * @param mixed $default_value The value to return if it doesn't exist
         *
         * @return mixed
         */
        public function getJsonBodyParameter($key, $default_value = null)
        {
            return $this->_json_body_parameters[$key] ?? $default_value;
        }

        /**
         * Get a parameter from the request
         *
         * @param string $key The parameter you want to retrieve
         * @param mixed $default_value The value to return if it doesn't exist
         * @param boolean $sanitized Whether to sanitize strings or not
         *
         * @return mixed
         */
        public function getParameter($key, $default_value = null, $sanitized = true)
        {
            if (isset($this->_json_body_parameters[$key])) {
                return $this->_json_body_parameters[$key];

            } elseif (isset($this->_request_parameters[$key])) {

                if ($sanitized && is_string($this->_request_parameters[$key])) {
                    return $this->__sanitize_string($this->_request_parameters[$key]);
                } elseif ($sanitized) {
                    return $this->__sanitize_params($this->_request_parameters[$key]);
                } else {
                    return $this->_request_parameters[$key];
                }
            } else {
                return $default_value;
            }
        }

        /**
         * Retrieve an unsanitized request parameter
         *
         * @param string $key The parameter you want to retrieve
         * @param mixed $default_value [optional] The value to return if it doesn't exist
         *
         * @return mixed
         * @see getParameter
         *
         */
        public function getRawParameter($key, $default_value = null)
        {
            return $this->getParameter($key, $default_value, false);
        }

        /**
         * Retrieve a cookie
         *
         * @param string $key The cookie to retrieve
         * @param mixed $default_value The value to return if it doesn't exist
         *
         * @return mixed
         */
        public function getCookie($key, $default_value = null)
        {
            return (isset($this->_cookies[$key])) ? $this->_cookies[$key] : $default_value;
        }

        /**
         * Check to see if a request parameter is set
         *
         * @param string $key The parameter to check for
         *
         * @return boolean
         */
        public function hasParameter($key)
        {
            return array_key_exists($key, $this->_json_body_parameters) || array_key_exists($key, $this->_request_parameters);
        }

        /**
         * Check to see if a cookie is set
         *
         * @param string $key The cookie to check for
         *
         * @return boolean
         */
        public function hasCookie($key)
        {
            return (bool)($this->getCookie($key) !== null);
        }

        /**
         * Set a request parameter
         *
         * @param string $key The parameter to set
         * @param mixed $value The value to set it too
         */
        public function setParameter($key, $value)
        {
            $this->_request_parameters[$key] = $value;
        }

        /**
         * Get the current request method
         *
         * @return integer
         */
        public function getMethod()
        {
            switch (mb_strtolower($_SERVER['REQUEST_METHOD'])) {
                case 'get':
                    return self::GET;
                    break;
                case 'post':
                    return self::POST;
                    break;
                case 'delete':
                    return self::DELETE;
                    break;
                case 'put':
                    return self::PUT;
                    break;
                case 'options':
                    return self::OPTIONS;
                    break;
            }
        }

        /**
         * Check if the current request method is $method
         *
         * @param $method
         *
         * @return boolean
         */
        public function isMethod($method)
        {
            return ($this->getMethod() == $method) ? true : false;
        }

        public function isPost()
        {
            return $this->isMethod(self::POST);
        }

        public function isOptions()
        {
            return $this->isMethod(self::OPTIONS);
        }

        public function isDelete()
        {
            return $this->isMethod(self::DELETE);
        }

        public function isPut()
        {
            return $this->isMethod(self::PUT);
        }

        /**
         * Check if the current request is an ajax call
         *
         * @return boolean
         */
        public function isAjaxCall()
        {
            return $this->_is_ajax_call;
        }

        /**
         * Sanitize a string
         *
         * @param string $string The string to sanitize
         *
         * @return string the sanitized string
         */
        protected function __sanitize_string($string)
        {
            try {
                $charset = (class_exists('Caspar')) ? Caspar::getI18n()->getCharset() : 'utf-8';
            } catch (Exception $e) {
                $charset = 'utf-8';
            }
            return htmlspecialchars($string, ENT_QUOTES, $charset);
        }

        /**
         * Wrapper around __sanitize_string method
         *
         * @param string $string The string to sanitize
         *
         * @return string the sanitized string
         */
        public function sanitize_input($string)
        {
            return $this->__sanitize_string($string);
        }

        public function getRequestedFormat()
        {
            return $this->getParameter('format', 'html');
        }

        public function offsetExists($offset): bool
        {
            return $this->hasParameter($offset);
        }

        public function offsetGet($offset): mixed
        {
            return $this->getParameter($offset);
        }

        public function offsetSet($offset, $value): void
        {
            $this->setParameter($offset, $value);
        }

        public function offsetUnset($offset): void
        {
            $this->setParameter($offset, null);
        }

        public function getQueryString()
        {
            return $this->_query_string;
        }

    }

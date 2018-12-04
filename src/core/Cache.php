<?php

	namespace caspar\core;

	/**
	 * Cache class
	 *
	 * @author Daniel Andre Eikeland <zegenie@gmail.com>
	 * @version 1.0
	 * @license http://www.opensource.org/licenses/mozilla1.1.php Mozilla Public License 1.1 (MPL 1.1)
	 * @package caspar
	 * @subpackage core
	 */

	/**
	 * Cache class
	 *
	 * @package caspar
	 * @subpackage core
	 */
	class Cache
	{

		const TYPE_DUMMY = 0;
		const TYPE_APC = 1;
		const TYPE_FILE = 2;

		/**
		 * @var bool
		 */
		protected $enabled = true;

		protected $type;

		protected $path;

		public function __construct($type, $options = [])
		{
			$this->type = $type;

			if (isset($options['enabled'])) {
				$this->enabled = $options['enabled'];
			}

			if (isset($options['path'])) {
				if (!file_exists($options['path'])) {
					throw new \RuntimeException("Configured cache path ({$options['path']}) is not writable. Please check your configuration.");
				}

				$this->path = $options['path'];
			}
		}

		/**
		 * @return string
		 */
		public function getCacheTypeDescription()
		{
			switch ($this->type) {
				case self::TYPE_DUMMY:
					return 'Dummy cache';
				case self::TYPE_APC:
					return 'In-memory cache (apc)';
				case self::TYPE_FILE:
					return 'File cache (' . $this->path . ')';
			}

			return 'Invalid cache type';
		}

		/**
		 * @return int
		 */
		public function getType()
		{
			return $this->type;
		}

		/**
		 * @param string $key The cache key to look up
		 *
		 * @return mixed
		 */
		public function get($key)
		{
			if (!$this->enabled) return null;

			switch ($this->type) {
				case self::TYPE_APC:
					$success = false;
					$var = apc_fetch($key, $success);

					return ($success) ? $var : null;
				case self::TYPE_FILE:
					$filename = $this->path . $key . '.cache';
					if (!file_exists($filename)) return null;

					$value = unserialize(file_get_contents($filename));
					return $value;
				case self::TYPE_DUMMY:
				default:
					return null;
			}
		}

		/**
		 * @param string $key The cache key to look up
		 *
		 * @return bool
		 */
		public function has($key)
		{
			if (!$this->enabled) return false;

			switch ($this->type) {
				case self::TYPE_APC:
					$success = false;
					apc_fetch($key, $success);
					break;
				case self::TYPE_FILE:
					$filename = $this->path . $key . '.cache';
					$success = file_exists($filename);
					break;
				case self::TYPE_DUMMY:
				default:
					$success = false;
			}

			return $success;
		}

		/**
		 * Store an item in the cache
		 *
		 * @param string $key The cache key to store the item under
		 * @param mixed $value The value to store
		 *
		 * @return bool
		 */
		public function set($key, $value)
		{
			if (!$this->enabled) {
				Logging::log('Key "' . $key . '" not cached (cache disabled)', 'cache');

				return false;
			}

			Logging::log('Caching value for key "' . $key . '"', 'cache');

			switch ($this->type) {
				case self::TYPE_APC:
					apc_store($key, $value);
					break;
				case self::TYPE_FILE:
					$filename = $this->path . $key . '.cache';
					file_put_contents($filename, serialize($value));
					break;
			}


			return true;
		}

		/**
		 * Delete an entry from the cache
		 *
		 * @param string $key The cache key to delete
		 */
		public function delete($key)
		{
			if (!$this->enabled) return;

			switch ($this->type) {
				case self::TYPE_APC:
					apc_delete($key);
					break;
				case self::TYPE_FILE:
					$filename = $this->path . $key . '.cache';
					unlink($filename);
			}
		}
		
	}

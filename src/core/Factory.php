<?php

	namespace caspar\core;

	/**
	 * Static factory class
	 *
	 * @author Daniel Andre Eikeland <zegenie@gmail.com>
	 * @version 1.0
	 * @license http://www.opensource.org/licenses/mozilla1.1.php Mozilla Public License 1.1 (MPL 1.1)
	 * @package caspar
	 * @subpackage core
	 */

	/**
	 * Static factory class
	 *
	 * This factory class manufactures and returns objects without instantiating
	 * new objects whenever this is needed.
	 *
	 * @package caspar
	 * @subpackage core
	 */
	final class Factory
	{

		public function __call($name, $arguments)
		{
			if (class_exists($name))
			{
				array_unshift($arguments, $name);
				return call_user_func_array(array($this, "manufacture"), $arguments);
			}
			else
			{
				throw new \Exception("The class $name doesn't exist");
			}
		}

		public function manufacture($classname, $id, $row = null)
		{
			// Check that the id is valid
			if ((int) $id == 0) throw new \Exception('Invalid id');

			// Set up the name for the factory array
			$factory_array_name = "_{$classname}s";
			$item = null;

			// Set up the manufactured array if it doesn't exist
			if (!isset($this->$factory_array_name))
			{
				Logging::log("Setting up manufactured array for $classname");
				$this->$factory_array_name = array();
			}

			// If the current id doesn't exist in the manufactured array, manufacture it
			if (!array_key_exists($id, $this->$factory_array_name))
			{
				// Initialize a position for the item in the manufactured array
				$this->{$factory_array_name}[$id] = null;

				try
				{
					// Check if the class is cacheable as well
					$cacheable = in_array($classname, array('TBGProject', 'TBGStatus', 'TBGPriority', 'TBGCategory', 'TBGUserstate'));
					$item = null;

					// If the class is cacheable, check if it exists in the cache
					if ($cacheable)
					{
						if ($item = Cache::get("TBGFactory_cache{$factory_array_name}_{$id}"))
						{
							Logging::log("Using cached $classname with id $id");
						}
					}

					// If we didn't get an item from the cache, manufacture it
					if (!$cacheable || !is_object($item))
					{
						$item = new $classname($id, $row);
						Logging::log("Manufacturing $classname with id $id");

						// Add the item to the cache if it's cacheable
						if ($cacheable)
						{
							Cache::add("TBGFactory_cache{$factory_array_name}_{$id}", $item);
						}
					}

					// Add the manufactured item to the manufactured array
					$this->{$factory_array_name}[$id] = $item;
				}
				catch (Exception $e)
				{
					throw $e;
				}
			}
			else
			{
				Logging::log("Using previously manufactured $classname with id $id");
			}

			// Return the item at that id in the manufactured array
			return $this->{$factory_array_name}[$id];
		}

	}

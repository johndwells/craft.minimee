<?php
namespace SelvinOrtiz\Zit;

/**
 * @=SelvinOrtiz\Zit
 *
 * Tiny dependency management library for PHP 5.3
 *
 * @author		Selvin Ortiz <selvin@selvinortiz.com>
 * @package		Zit
 * @version		0.5.0
 * @category	DI, IoC (PHP)
 * @copyright	2014 Selvin Ortiz
 */

class Zit implements IZit
{
	protected static $instances;

	protected $services		= array();
	protected $callables	= array();

	protected function __construct() 	{}
	protected function __clone()		{}

	/**
	 * Instantiate Zit or the extending, statically called class
	 *
	 * @return	object
	 */
	public static function getInstance()
	{
		$calledClass = get_called_class();

		if ( ! isset(static::$instances[$calledClass]))
		{
			static::$instances[$calledClass] = new $calledClass;
		}

		return static::$instances[$calledClass];
	}

	/**
	 * Binds the service generator and resolves it by its id
	 *
	 * @param	string		$id					The service generator id
	 * @param	Callable	$serviceGenerator	The service generator closure/function
	 */
	public function bind($id, \Closure $serviceGenerator)
	{
		$this->callables[(string) $id] = function($zit) use($serviceGenerator)
		{
			static $object;

			if (null === $object)
			{
				$object = $serviceGenerator($zit);
			}

			return $object;
		};
	}

	/**
	 * Stashes away a service instance and resolves it by its id
	 *
	 * @param	string	$id					The service instance id
	 * @param	object	$serviceInstance	The service instance
	 */
	public function stash($id, $serviceInstance)
	{
		if (is_object($serviceInstance))
		{
			$this->services[$id] = $serviceInstance;
		}
	}

	/**
	 * Binds the callable function and executes it by its id
	 *
	 * @param	string	$id			The callable function id
	 * @param	Closure	$callable	The callable function
	 */
	public function extend( $id, \Closure $callable )
	{
		if (is_callable( $callable) || method_exists( $callable, '__invoke'))
		{
			$this->callables[$id] = $callable;
		}
	}

	protected function pop($id, $args=array() )
	{
		if (array_key_exists($id, $this->services))
		{
			return $this->services[$id];
		}

		if (array_key_exists( $id, $this->callables))
		{
			$callable = $this->callables[$id];

			return call_user_func_array($callable, array_merge(array($this), $args));
		}

		throw new \Exception("The dependency with id of ({$id}) is missing.");
	}

	public function __get($id)
	{
		if (property_exists($this, $id))
		{
			return $this->{$id};
		}
		else
		{
			return $this->pop($id);
		}
	}

	public function __call($id, $args=array())
	{
		return $this->pop($id, $args);
	}

	public static function __callStatic($id, $args=array())
	{
		return static::getInstance()->pop( $id, $args );
	}
}

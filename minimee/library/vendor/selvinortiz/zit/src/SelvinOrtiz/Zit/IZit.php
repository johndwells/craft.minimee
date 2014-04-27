<?php
namespace SelvinOrtiz\Zit;

interface IZit
{
	/**
	 * Must bind the service generator and resolve it by its id
	 *
	 * @param	string	$id					The service generator id
	 * @param	Closure	$serviceGenerator	The service generator closure/function
	 */
	public function bind( $id, \Closure $serviceGenerator );

	/**
	 * Must stash away a service instance and resolve it by its id
	 *
	 * @param	string	$id					The service instance id
	 * @param	object	$serviceInstance	The service instance
	 */
	public function stash( $id, $serviceInstance );

	/**
	 * Must bind the callable function and execute it by its id
	 *
	 * @param	string	$id					The callable function id
	 * @param	Closure	$callable			The callable function
	 */
	public function extend( $id, \Closure $callable );
}

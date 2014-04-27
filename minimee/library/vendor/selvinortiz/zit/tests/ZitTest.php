<?php
use SelvinOrtiz\Zit\Zit;

class ZitTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {}

	public function tearDown() {}

	public function inspect($data)
	{
		fwrite( STDERR, print_r($data) );
	}

	public function testBind()
	{
		$zit = Zit::getInstance();

		$zit->bind( 'myServiceGenerator', function( $zit ) {
			$instance		= new \stdClass;
			$instance->id	= 'myServiceGeneratorId';

			return $instance;
		});

		$this->assertTrue( $zit->myServiceGenerator() instanceof \stdClass );
		$this->assertTrue( $zit->myServiceGenerator()->id === 'myServiceGeneratorId' );
	}

	public function testStash()
	{
		$zit = Zit::getInstance();

		$instance		= new \stdClass;
		$instance->id	= 'myServiceId';

		$zit->stash( 'myService', $instance );

		$this->assertTrue( $zit->myService instanceof \stdClass );
		$this->assertTrue( $zit->myService() instanceof \stdClass );
		$this->assertTrue( $zit->myService()->id === 'myServiceId' );
	}

	public function testExtend()
	{
		$zit = Zit::getInstance();

		$zit->extend( 'myCallable', function() {
			return 12345;
		});

		$this->assertTrue( $zit->myCallable() === 12345 );
		$this->assertTrue( Zit::myCallable() === 12345 );
	}
}

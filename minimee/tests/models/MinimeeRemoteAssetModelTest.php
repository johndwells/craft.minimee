<?php
namespace Craft;

use \Guzzle\Http\Client as Client;
use \Guzzle\Plugin\Mock\MockPlugin as MockPlugin;
use \Guzzle\Http\Message\Response as Response;
use \SelvinOrtiz\Zit\Zit;
use \Mockery as m;

class MinimeeRemoteAssetModelTest extends MinimeeBaseTest
{
	protected $_model;

	/**
	 * Called at the start of each test run; helps bootstrap our tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		minimee()->extend('makeRemoteAssetModel', function(Zit $zit, $attributes = array(), $client = null) {
			return new Minimee_RemoteAssetModel($attributes, $client);
		});

		minimee()->extend('makeClient', function() {
			return new Client;
		});
	}

	public function testGetContentsSendsRequestOnlyOnce()
	{
		minimee()->extend('makeClient', function() {
			$mock = new MockPlugin();
			$mock->addResponse(new Response(200, array(), '* { color: red }'));
			$mock->addResponse(new Response(404));

			$client = new Client();
			$client->addSubscriber($mock);

			return $client;
		});

		$remoteAsset = minimee()->makeRemoteAssetModel(array());

		$this->assertEquals('* { color: red }', $remoteAsset->contents);
		$this->assertEquals('* { color: red }', $remoteAsset->contents);
	}

	/**
     * @expectedException Exception
     */
	public function testGetContentsIfNotExists()
	{
		minimee()->extend('makeClient', function() {
			$mock = new MockPlugin();
			$mock->addResponse(new Response(404));

			$client = new Client();
			$client->addSubscriber($mock);

			return $client;
		});

		$remoteAsset = minimee()->makeRemoteAssetModel(array(
			'filenamePath' => 'http://domain.dev/thisfilewillnotexist'
		));

		$contents = $remoteAsset->contents;
	}
	
	public function testGetContentsIfExists()
	{
		minimee()->extend('makeClient', function() {
			$mock = new MockPlugin();
			$mock->addResponse(new Response(200, array(), '* { color: red }'));

			$client = new Client();
			$client->addSubscriber($mock);

			return $client;
		});

		$remoteAsset = minimee()->makeRemoteAssetModel(array());

		$this->assertEquals('* { color: red }', $remoteAsset->contents);
	}

	public function testGetLastTimeModifiedIsAlwaysZero()
	{
		$this->_populateWith(array());

		$lastTimeModified = $this->_model->lastTimeModified;

		$this->assertSame(0, $lastTimeModified->getTimestamp());
	}

	public function testExistsIsAlwaysTrue()
	{
		$this->_populateWith(array());

		$this->assertTrue($this->_model->exists());
	}

	public function testToStringReturnsFilename()
	{
		$this->_populateWith(array(
			'filename' => 'http://domain.com/assets/style.css'
		));

		$this->assertEquals('http://domain.com/assets/style.css', sprintf($this->_model));
	}

	public function testSetFilenamePathRemovesDoubleSlashes()
	{
		$this->_populateWith(array());

		$this->_model->filenamePath = 'http://domain.com///cache';
		$this->assertEquals('http://domain.com/cache', $this->_model->filenamePath);
	}

	public function testSetFilenamePathRemovesDoubleSlashesProtocolRelative()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = '//domain.com///cache';
		$this->assertEquals('//domain.com/cache', $this->_model->filenameUrl);
	}

	public function testSetFilenameUrlRemovesDoubleSlashes()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = 'http://domain.com///cache';
		$this->assertEquals('http://domain.com/cache', $this->_model->filenameUrl);
	}

	public function testSetFilenameUrlRemovesDoubleSlashesProtocolRelative()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = '//domain.com///cache';
		$this->assertEquals('//domain.com/cache', $this->_model->filenameUrl);
	}

	/**
	 * Internal method for shorthand populating our Minimee_RemoteAssetModel
	 * 
	 * @param Array $attributes
	 * @return Minimee_RemoteAssetModel
	 */
	protected function _populateWith($attributes)
	{
		$this->_model = minimee()->makeRemoteAssetModel($attributes);
	}
}

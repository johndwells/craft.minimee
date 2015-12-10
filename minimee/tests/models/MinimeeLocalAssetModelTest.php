<?php
namespace Craft;

use \Mockery as m;
use \SelvinOrtiz\Zit\Zit;

class MinimeeLocalAssetModelTest extends MinimeeBaseTest
{
	protected $_model;

	/**
	 * Called at the start of each test run; helps bootstrap our tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		minimee()->extend('makeLocalAssetModel', function(Zit $zit, $attributes = array()) {
			return new Minimee_LocalAssetModel($attributes);
		});
	}

	/**
     * @expectedException Exception
     */
	public function testGetContentsThrowsExeptionWhenNotExists()
	{
		$this->_populateWith(array(
			'filenamePath' => __DIR__ . '/../assets/responseCodes/404.txt'
		));

		$contents = $this->_model->contents;
	}

	public function testGetContentsWhenExists()
	{
		$this->_populateWith(array(
			'filenamePath' => __DIR__ . '/../assets/responseCodes/200.txt'
		));

		$this->assertEquals('200', $this->_model->contents);
	}

	public function testExistsUpdatesFilenamePath()
	{
		$filenamePath = __DIR__ . '/../assets/responseCodes/200.txt';

		$this->_populateWith(array(
			'filenamePath' => $filenamePath
		));

		$this->_model->exists();

		$this->assertNotEquals($filenamePath, $this->_model->filenamePath);
	}

	public function testGetTimestampWhenExists()
	{
		$this->_populateWith(array(
			'filenamePath' => __DIR__ . '/../assets/responseCodes/200.txt'
		));

		$this->assertInstanceOf('DateTime', $this->_model->lastTimeModified);
	}

	/**
     * @expectedException Exception
     */
	public function testGetTimestampWhenNotExists()
	{
		$this->_populateWith(array(
			'filenamePath' => __DIR__ . '/../assets/responseCodes/404.txt'
		));

		$lastTimeModified = $this->_model->lastTimeModified;
	}

	public function testExistsIsTrue()
	{
		$this->_populateWith(array(
			'filenamePath' => __DIR__ . '/../assets/responseCodes/200.txt'
		));

		$this->assertNotEquals(false, $this->_model->exists());
	}

	public function testExistsIsFalse()
	{
		$this->_populateWith(array(
			'filenamePath' => __DIR__ . '/../assets/responseCodes/404.txt'
		));

		$this->assertNotEquals(true, $this->_model->exists());
	}

	public function testToStringReturnsFilename()
	{
		$this->_populateWith(array(
			'filename' => '/assets/responseCodes/200.txt'
		));

		$this->assertEquals('/assets/responseCodes/200.txt', sprintf($this->_model));
	}

	public function testSetFilenamePathRemovesDoubleSlashes()
	{
		$this->_populateWith(array());

		$this->_model->filenamePath = '/////path////to////file////';
		$this->assertEquals('/path/to/file/', $this->_model->filenamePath);
	}

	public function testSetFilenameUrlRemovesDoubleSlashes()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = 'http://craft.dev///cache';
		$this->assertEquals('http://craft.dev/cache', $this->_model->filenameUrl);
	}

	public function testSetFilenameUrlRemovesDoubleSlashesProtocolRelative()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = '//craft.dev///cache';
		$this->assertEquals('//craft.dev/cache', $this->_model->filenameUrl);
	}

	/**
	 * Internal method for shorthand populating our Minimee_LocalAssetModel
	 * 
	 * @param Array $attributes
	 * @return Minimee_LocalAssetModel
	 */
	protected function _populateWith($attributes)
	{
		$this->_model = minimee()->makeLocalAssetModel($attributes);
	}
}

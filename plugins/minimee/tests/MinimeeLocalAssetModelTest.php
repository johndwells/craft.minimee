<?php
namespace Craft;

use \Mockery as m;

class MinimeeLocalAssetModelTest extends BaseTest
{
	protected $_model;

	/**
	 * Called at the start of each test run; helps bootstrap our tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		require_once __DIR__ . '/../models/Minimee_AssetBaseModel.php';
		require_once __DIR__ . '/../models/Minimee_LocalAssetModel.php';

		// to avoid?
		// Notice: Undefined index: SERVER_SOFTWARE in /Users/John/Sites/craft.dev/craft/app/helpers/AppHelper.php on line 31
        $_SERVER['SERVER_SOFTWARE'] = 'Apache';
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

		$this->_model->filenameUrl = 'http://domain.com///cache';
		$this->assertEquals('http://domain.com/cache', $this->_model->filenameUrl);
	}

	public function testSetFilenameUrlRemovesDoubleSlashesProtocolRelative()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = '//domain.com///cache';
		$this->assertEquals('//domain.com/cache', $this->_model->filenameUrl);
	}

	protected function _inspect($data)
	{
		fwrite(STDERR, print_r($data));
	}

	/**
	 * Internal method for shorthand populating our Minimee_LocalAssetModel
	 * 
	 * @param Array $attributes
	 * @return Minimee_LocalAssetModel
	 */
	protected function _populateWith($attributes)
	{
		$this->_model = Minimee_LocalAssetModel::populateModel($attributes);
	}
}
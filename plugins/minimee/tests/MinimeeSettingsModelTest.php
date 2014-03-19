<?php
namespace Craft;

use \Mockery as m;

class MinimeeSettingsModelTest extends BaseTest
{
	protected $_model;

	/**
	 * Called at the start of each test run; helps bootstrap our tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		require_once __DIR__ . '/../models/Minimee_SettingsModel.php';

		// to avoid?
		// Notice: Undefined index: SERVER_SOFTWARE in /Users/John/Sites/craft.dev/craft/app/helpers/AppHelper.php on line 31
        $_SERVER['SERVER_SOFTWARE'] = 'Apache';
	}

	public function testUseResourceCacheWhenBothNonEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => 'http://domain.com/cache'
		));

		$this->assertEquals(false, $this->_model->useResourceCache());
	}

	public function testUseResourceCacheWhenOneIsEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => 'http://domain.com/cache'
		));

		$this->assertEquals(false, $this->_model->useResourceCache());

		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => ''
		));

		$this->assertEquals(false, $this->_model->useResourceCache());
	}

	public function testUseResourceCacheWhenBothEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => ''
		));

		$this->assertEquals(true, $this->_model->useResourceCache());
	}

	public function testValidateCachePathAndUrlWhenBothEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => ''
		));

		$this->_model->validateCachePathAndUrl();
		$this->assertEquals(false, $this->_model->hasErrors());
	}

	public function testValidateCachePathAndUrlWhenBothNonEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => 'http://domain.com/cache'
		));

		$this->_model->validateCachePathAndUrl();
		$this->assertEquals(false, $this->_model->hasErrors());
	}

	public function testValidateCachePathAndUrlWhenOneIsEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => 'http://domain.com/cache'
		));

		$this->_model->validateCachePathAndUrl();
		$this->assertEquals(true, $this->_model->hasErrors());

		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => ''
		));

		$this->_model->validateCachePathAndUrl();
		$this->assertEquals(true, $this->_model->hasErrors());
	}

	protected function _inspect($data)
	{
		fwrite(STDERR, print_r($data));
	}

	/**
	 * Internal method for shorthand populating our Minimee_SettingsModel
	 * 
	 * @param Array $attributes
	 * @return Minimee_SettingsModel
	 */
	protected function _populateWith($attributes)
	{
		$this->_model = Minimee_SettingsModel::populateModel($attributes);
	}
}
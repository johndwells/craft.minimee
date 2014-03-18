<?php
namespace Craft;

use \Mockery as m;

class MinimeeSettingsModelTest extends BaseTest
{
	/**
	 * Called at the start of each test run; helps bootstrap our tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		$craft = m::mock('Craft\Craft');
		$craft->shouldReceive('t')->andReturn('anything');

		require_once dirname(__FILE__) . '/../models/Minimee_SettingsModel.php';
	}

	public function testUseResourceCacheWhenBothNonEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => 'http://domain.com/cache'
		));

		$this->assertEquals(false, $this->model->useResourceCache());
	}

	public function testUseResourceCacheWhenOneIsEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => 'http://domain.com/cache'
		));

		$this->assertEquals(false, $this->model->useResourceCache());

		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => ''
		));

		$this->assertEquals(false, $this->model->useResourceCache());
	}

	public function testUseResourceCacheWhenBothEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => ''
		));

		$this->assertEquals(true, $this->model->useResourceCache());
	}

	public function testValidateCachePathAndUrlWhenBothEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => ''
		));

		$this->model->validateCachePathAndUrl();
		$this->assertEquals(false, $this->model->hasErrors());
	}

	public function testValidateCachePathAndUrlWhenBothNonEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => 'http://domain.com/cache'
		));

		$this->model->validateCachePathAndUrl();
		$this->assertEquals(false, $this->model->hasErrors());
	}

	public function testValidateCachePathAndUrlWhenOneIsEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => 'http://domain.com/cache'
		));

		$this->model->validateCachePathAndUrl();
		$this->assertEquals(true, $this->model->hasErrors());

		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => ''
		));

		$this->model->validateCachePathAndUrl();
		$this->assertEquals(true, $this->model->hasErrors());
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
		$this->model = Minimee_SettingsModel::populateModel($attributes);
	}
}
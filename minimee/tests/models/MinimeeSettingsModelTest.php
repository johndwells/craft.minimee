<?php
namespace Craft;

use \Mockery as m;
use \SelvinOrtiz\Zit\Zit;

class MinimeeSettingsModelTest extends MinimeeBaseTest
{
	protected $_model;

	/**
	 * Called at the start of each test run; helps bootstrap our tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		minimee()->extend('makeSettingsModel', function(Zit $zit, $attributes = array()) {
			return new Minimee_SettingsModel($attributes);
		});
	}

	public function testGetReturnTypeWhenEmpty()
	{
		$this->_populateWith(array(
			'returnType' => ''
		));

		$defaultReturnType = $this->_model->defineAttributes()['returnType']['default'];

		$this->assertEquals($defaultReturnType, $this->_model->returnType);
	}

	public function testGetReturnTypeWhenNotEmpty()
	{
		$this->_populateWith(array(
			'returnType' => 'returnType'
		));

		$this->assertEquals('returnType', $this->_model->returnType);
	}

	public function testGetTagTemplatesWhenEmpty()
	{
		$this->_populateWith(array(
			'cssReturnTemplate' => '',
			'jsReturnTemplate' => ''
		));

		$defaultcssReturnTemplate = $this->_model->defineAttributes()['cssReturnTemplate']['default'];
		$defaultjsReturnTemplate = $this->_model->defineAttributes()['jsReturnTemplate']['default'];

		$this->assertEquals($defaultcssReturnTemplate, $this->_model->cssReturnTemplate);
		$this->assertEquals($defaultjsReturnTemplate, $this->_model->jsReturnTemplate);
	}

	public function testGetTagTemplatesWhenNotEmpty()
	{
		$this->_populateWith(array(
			'cssReturnTemplate' => 'cssReturnTemplate',
			'jsReturnTemplate' => 'jsReturnTemplate'
		));

		$this->assertEquals('cssReturnTemplate', $this->_model->cssReturnTemplate);
		$this->assertEquals('jsReturnTemplate', $this->_model->jsReturnTemplate);
	}

	public function testGetCssPrependUrlWithValue()
	{
		$this->_populateWith(array(
			'cssPrependUrl' => 'http://craft.dev/assets/css/'
		));

		$this->assertEquals('http://craft.dev/assets/css/', $this->_model->cssPrependUrl);
	}

	public function testGetCssPrependUrlWithoutValue()
	{
		$this->_populateWith(array());

		$this->assertEquals(false, $this->_model->cssPrependUrl);
	}

	public function testGetCssPrependUrlHasTrailingSlash()
	{
		$this->_populateWith(array(
			'cssPrependUrl' => 'http://craft.dev/assets/css/'
		));

		$this->assertEquals('http://craft.dev/assets/css/', $this->_model->cssPrependUrl);

		$this->_populateWith(array(
			'cssPrependUrl' => 'http://craft.dev/assets/css'
		));

		$this->assertEquals('http://craft.dev/assets/css/', $this->_model->cssPrependUrl);
	}

	public function testGetCssPrependUrlParsesEnvironmentVariable()
	{
		$config = m::mock('Craft\ConfigService')->makePartial();
		$config->shouldReceive('parseEnvironmentString')->with('{stringToParse}')->andReturn('http://craft.dev/assets/css/');
		$this->setComponent(craft(), 'config', $config);

		$this->_populateWith(array(
			'cssPrependUrl' => '{stringToParse}'
		));

		$this->assertEquals('http://craft.dev/assets/css/', $this->_model->cssPrependUrl);
	}

	public function testGetFilesystemPathWithValue()
	{
		$this->_populateWith(array(
			'filesystemPath' => '/some/path/to/craft.dev/'
		));

		$this->assertEquals('/some/path/to/craft.dev/', $this->_model->filesystemPath);
	}

	public function testGetFilesystemPathWithoutValue()
	{
		$this->_populateWith(array());

		$this->assertEquals('/some/path/to/craft.dev/', $this->_model->filesystemPath);
	}

	public function testGetFilesystemPathHasTrailingSlash()
	{
		$this->_populateWith(array(
			'filesystemPath' => '/some/path/to/craft.dev/'
		));

		$this->assertEquals('/some/path/to/craft.dev/', $this->_model->filesystemPath);

		$this->_populateWith(array(
			'filesystemPath' => '/some/path/to/craft.dev'
		));

		$this->assertEquals('/some/path/to/craft.dev/', $this->_model->filesystemPath);
	}

	public function testGetFilesystemPathParsesEnvironmentVariable()
	{
		$config = m::mock('Craft\ConfigService')->makePartial();
		$config->shouldReceive('parseEnvironmentString')->with('{stringToParse}')->andReturn('/some/path/to/craft.dev/');
		$this->setComponent(craft(), 'config', $config);

		$this->_populateWith(array(
			'filesystemPath' => '{stringToParse}'
		));

		$this->assertEquals('/some/path/to/craft.dev/', $this->_model->filesystemPath);
	}

	public function testGetBaseUrlWithValue()
	{
		$this->_populateWith(array(
			'baseUrl' => 'http://craft.dev/'
		));

		$this->assertEquals('http://craft.dev/', $this->_model->baseUrl);
	}

	public function testGetBaseUrlWithoutValue()
	{
		$this->_populateWith(array());

		$this->assertEquals('http://craft.dev/', $this->_model->baseUrl);
	}

	public function testGetBaseUrlHasTrailingSlash()
	{
		$this->_populateWith(array(
			'baseUrl' => 'http://craft.dev'
		));

		$this->assertEquals('http://craft.dev/', $this->_model->baseUrl);

		$this->_populateWith(array(
			'baseUrl' => 'http://craft.dev/'
		));

		$this->assertEquals('http://craft.dev/', $this->_model->baseUrl);
	}

	public function testGetBaseUrlParsesEnvironmentVariable()
	{
		$config = m::mock('Craft\ConfigService')->makePartial();
		$config->shouldReceive('parseEnvironmentString')->with('{stringToParse}')->andReturn('http://craft.dev/');
		$this->setComponent(craft(), 'config', $config);

		$this->_populateWith(array(
			'baseUrl' => '{stringToParse}'
		));

		$this->assertEquals('http://craft.dev/', $this->_model->baseUrl);
	}

	public function testGetCachePathReturnsFalseWithoutValue()
	{
		$this->_populateWith(array());

		$this->assertEquals(false, $this->_model->cachePath);
	}

	public function testGetCachePathHasTrailingSlash()
	{
		$this->_populateWith(array(
			'cachePath' => '/some/path/to/craft.dev/cache'
		));

		$this->assertEquals('/some/path/to/craft.dev/cache/', $this->_model->cachePath);

		$this->_populateWith(array(
			'cachePath' => '/some/path/to/craft.dev/cache/'
		));

		$this->assertEquals('/some/path/to/craft.dev/cache/', $this->_model->cachePath);
	}

	public function testGetCachePathParsesEnvironmentVariable()
	{
		$config = m::mock('Craft\ConfigService')->makePartial();
		$config->shouldReceive('parseEnvironmentString')->with('{stringToParse}')->andReturn('/some/path/to/craft.dev/cache/');
		$this->setComponent(craft(), 'config', $config);

		$this->_populateWith(array(
			'cachePath' => '{stringToParse}'
		));

		$this->assertEquals('/some/path/to/craft.dev/cache/', $this->_model->cachePath);
	}

	public function testGetCacheUrlReturnsFalseWithoutValue()
	{
		$this->_populateWith(array());
		$this->assertEquals(false, $this->_model->cacheUrl);
	}

	public function testGetCacheUrlHasTrailingSlash()
	{
		$this->_populateWith(array(
			'cacheUrl' => 'http://craft.dev/cache'
		));

		$this->assertEquals('http://craft.dev/cache/', $this->_model->cacheUrl);

		$this->_populateWith(array(
			'cacheUrl' => 'http://craft.dev/cache/'
		));

		$this->assertEquals('http://craft.dev/cache/', $this->_model->cacheUrl);
	}

	public function testGetCacheUrlParsesEnvironmentVariable()
	{
		$config = m::mock('Craft\ConfigService')->makePartial();
		$config->shouldReceive('parseEnvironmentString')->with('{stringToParse}')->andReturn('http://craft.dev/cache/');
		$this->setComponent(craft(), 'config', $config);

		$this->_populateWith(array(
			'cacheUrl' => '{stringToParse}'
		));

		$this->assertEquals('http://craft.dev/cache/', $this->_model->cacheUrl);
	}

	public function testGetAttributeAccessors()
	{
		$model = \Mockery::mock('Craft\Minimee_SettingsModel')->makePartial();

		$model->shouldReceive('getCachePath')->andReturn(true);
		$model->shouldReceive('getCacheUrl')->andReturn(true);
		$model->shouldReceive('getBaseUrl')->andReturn(true);
		$model->shouldReceive('getFilesystemPath')->andReturn(true);

		$this->assertTrue($model->cachePath);
		$this->assertTrue($model->cacheUrl);
		$this->assertTrue($model->baseUrl);
		$this->assertTrue($model->filesystemPath);
	}

	public function testForceTrailingSlashWithSlash()
	{
		$model = minimee()->makeSettingsModel;

		$this->assertEquals('string/', $model->forceTrailingSlash('string/'));
	}

	public function testForceTrailingSlashWithoutSlash()
	{
		$model = minimee()->makeSettingsModel;

		$this->assertEquals('string/', $model->forceTrailingSlash('string'));
	}

	public function dataProviderZeroOne()
	{
		return [
			[0],
			[1]
		];
	}

	/**
	 * @dataProvider dataProviderZeroOne
	 */
	public function testPrepSettingsCastBools($zeroOne)
	{
		$model = minimee()->makeSettingsModel;

		// Bools are saved as 0s and 1s in DB
		$prepped = $model->prepSettings(
			array(
        		'enabled' => $zeroOne,
        		'combineCssEnabled' => $zeroOne,
        		'combineJsEnabled' => $zeroOne,
        		'minifyCssEnabled' => $zeroOne,
        		'minifyJsEnabled' => $zeroOne,
        		'cssPrependUrlEnabled' => $zeroOne
			)
		);

		$bool = (bool) $zeroOne;

		$this->_populateWith($prepped);

		$this->assertSame($bool, $this->_model->enabled);
		$this->assertSame($bool, $this->_model->combineCssEnabled);
		$this->assertSame($bool, $this->_model->combineJsEnabled);
		$this->assertSame($bool, $this->_model->minifyCssEnabled);
		$this->assertSame($bool, $this->_model->minifyJsEnabled);
		$this->assertSame($bool, $this->_model->cssPrependUrlEnabled);
	}

	public function testToStringReturnsOneOrZero()
	{
		$this->_populateWith(array(
			'enabled' => true
		));

		$this->assertSame('1', sprintf($this->_model));

		$this->_populateWith(array(
			'enabled' => false
		));

		$this->assertSame('0', sprintf($this->_model));
	}

	public function testUseResourceCacheWhenBothNonEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => 'http://craft.dev/cache'
		));

		$this->assertFalse($this->_model->useResourceCache());
	}

	public function testUseResourceCacheWhenOneIsEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => 'http://craft.dev/cache'
		));

		$this->assertFalse($this->_model->useResourceCache());

		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => ''
		));

		$this->assertFalse($this->_model->useResourceCache());
	}

	public function testUseResourceCacheWhenBothEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => ''
		));

		$this->assertTrue($this->_model->useResourceCache());
	}

	public function testValidateCachePathAndUrlWhenBothEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => ''
		));

		$this->assertTrue($this->_model->validate());
	}

	public function testValidateCachePathAndUrlWhenBothNonEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => 'http://craft.dev/cache'
		));

		$this->assertTrue($this->_model->validate());
	}

	public function testValidateCachePathAndUrlWhenOneIsEmpty()
	{
		$this->_populateWith(array(
			'cachePath' => '',
			'cacheUrl' => 'http://craft.dev/cache'
		));

		$this->_model->validateCachePathAndUrl();
		$this->assertTrue($this->_model->hasErrors());

		$this->_populateWith(array(
			'cachePath' => '/path/to/cache',
			'cacheUrl' => ''
		));

		$this->assertFalse($this->_model->validate());
		$this->assertTrue($this->_model->hasErrors());

		$errors = $this->_model->getErrors();
		$this->assertEquals(2, count($errors));
		$this->assertArrayHasKey('cachePath', $errors);
		$this->assertArrayHasKey('cacheUrl', $errors);
	}

	/**
	 * Internal method for shorthand populating our Minimee_SettingsModel
	 *
	 * @param Array $attributes
	 * @return Minimee_SettingsModel
	 */
	protected function _populateWith($attributes)
	{
		$this->_model = minimee()->makeSettingsModel($attributes);
	}
}

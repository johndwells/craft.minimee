<?php
namespace Craft;

use \Mockery as m;
use \SelvinOrtiz\Zit\Zit;

class MinimeeServiceTest extends MinimeeBaseTest
{
	public function setUp()
	{
		$this->_autoload();

		minimee()->stash('plugin', new MinimeePlugin);
		minimee()->stash('service', new MinimeeService);

		// these may be overridden during individual tests
		minimee()->extend('makeSettingsModel', function(Zit $zit, $attributes = array()) {
			return new Minimee_SettingsModel($attributes);
		});

		minimee()->extend('makeLocalAssetModel', function(Zit $zit, $attributes = array()) {
			return new Minimee_LocalAssetModel($attributes);
		});

		minimee()->extend('makeRemoteAssetModel', function(Zit $zit, $attributes = array()) {
			return new Minimee_RemoteAssetModel($attributes);
		});

        $this->config = m::mock('Craft\ConfigService');
        $this->config->shouldReceive('exists')->andReturn(false);
        $this->config->shouldReceive('getIsInitialized')->andReturn(true);
        $this->config->shouldReceive('getLocalized')->andReturn(true);
        $this->config->shouldReceive('get')->with('usePathInfo')->andReturn(true)->byDefault();
        $this->config->shouldReceive('get')->with('translationDebugOutput')->andReturn(false)->byDefault();
        $this->config->shouldReceive('get')->with('resourceTrigger')->andReturn('resource')->byDefault();
        $this->config->shouldReceive('get')->with('version')->andReturn('2.0');
        $this->config->shouldReceive('get')->with('translationDebugOutput')->andReturn(false);
        $this->config->shouldReceive('maxPowerCaptain')->andreturn(null);

        $this->setComponent(craft(), 'config', $this->config);

		// TODO: figure outo how to propery mock config so that we can run init()
		//minimee()->service->init();
	}

	/**
     * @expectedException Exception
     */
	public function testCheckHeadersWhenAllAssetsDoNotExist()
	{
		minimee()->extend('makeLocalAssetModel', function() {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(false);

			return $localAssetModelMock;
		});

		$assets = array(
			'/assets/css/style.1.css',
			'/assets/css/style.1.css'
		);

		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array($assets));

		$checkHeaders = $this->getMethod(minimee()->service, 'checkHeaders');
		$minimeeService = $checkHeaders->invoke(minimee()->service);
	}

	/**
     * @expectedException Exception
     */
	public function testCheckHeadersWhenAnyAssetDoesNotExist()
	{
		minimee()->extend('makeLocalAssetModel', function(Zit $sit, $attributes) {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();

			if($attributes['filename'] == '200')
			{
				$localAssetModelMock->shouldReceive('exists')->andReturn(true);
			}
			else
			{
				$localAssetModelMock->shouldReceive('exists')->andReturn(false);
			}

			return $localAssetModelMock;
		});

		$assets = array(
			'200',
			'404'
		);

		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array($assets));

		$checkHeaders = $this->getMethod(minimee()->service, 'checkHeaders');
		$minimeeService = $checkHeaders->invoke(minimee()->service);
	}

	public function testCheckHeadersWhenAllAssetsExist()
	{
		minimee()->extend('makeLocalAssetModel', function() {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(true);

			return $localAssetModelMock;
		});

		$assets = array(
			'/assets/css/style.1.css',
			'/assets/css/style.1.css'
		);

		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array($assets));

		$checkHeaders = $this->getMethod(minimee()->service, 'checkHeaders');
		$minimeeService = $checkHeaders->invoke(minimee()->service);
		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
	}

	public function testAppendToCacheBase()
	{
		$appendToCacheBase = $this->getMethod(minimee()->service, 'appendToCacheBase');

		$cacheBase1 = '/asset/css/file.1.css';
		$cacheBase2 = '/asset/css/file.2.css';

		$appendToCacheBase->invokeArgs(minimee()->service, array($cacheBase1));
		$this->assertEquals($cacheBase1, minimee()->service->cacheBase);

		$appendToCacheBase->invokeArgs(minimee()->service, array($cacheBase2));
		$this->assertEquals($cacheBase1 . $cacheBase2, minimee()->service->cacheBase);

	}

	public function testMinifyAssetWithCssRewritesUrlWhenMinifyCssEnabledIsTrue()
	{
		$assetContents = file_get_contents(__DIR__ . '/../assets/css/style.2.css');
		$assetContentsWrite = file_get_contents(__DIR__ . '/../assets/css/style.2.rewrite.min.css');

		minimee()->extend('makeLocalAssetModel', function() use ($assetContents) {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(true);
			$localAssetModelMock->shouldReceive('getAttribute')->with('filenameUrl')->andReturn('http://craft.dev/assets/css/style.2.css');
			$localAssetModelMock->shouldReceive('getContents')->andReturn($assetContents);

			return $localAssetModelMock;
		});

		minimee()->extend('makeSettingsModel', function() use ($assetContents) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('validate')->andReturn(true);
			$settingsModelMock->shouldReceive('attributeNames')->andreturn(array(
				'minifyCssEnabled',
				'cssPrependUrlEnabled',
				'cssPrependUrl'
			));
			$settingsModelMock->shouldReceive('getAttribute')->with('minifyCssEnabled')->andreturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrlEnabled')->andreturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrl')->andreturn('');

			return $settingsModelMock;
		});

		minimee()->service->settings = minimee()->makeSettingsModel();
		minimee()->service->type = MinimeeType::Css;

		$asset = minimee()->makeLocalAssetModel();

		$minifyAsset = $this->getMethod(minimee()->service, 'minifyAsset');
		$this->assertEquals($assetContentsWrite, $minifyAsset->invokeArgs(minimee()->service, array($asset)));
	}

	public function testMinifyAssetWithCssRewritesUrlWhenMinifyCssEnabledIsFalse()
	{
		$assetContents = file_get_contents(__DIR__ . '/../assets/css/style.2.css');
		$assetContentsWrite = file_get_contents(__DIR__ . '/../assets/css/style.2.rewrite.css');

		minimee()->extend('makeLocalAssetModel', function() use ($assetContents) {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(true);
			$localAssetModelMock->shouldReceive('getAttribute')->with('filenameUrl')->andReturn('http://craft.dev/assets/css/style.2.css');
			$localAssetModelMock->shouldReceive('getContents')->andReturn($assetContents);

			return $localAssetModelMock;
		});

		minimee()->extend('makeSettingsModel', function() use ($assetContents) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('validate')->andReturn(true);
			$settingsModelMock->shouldReceive('attributeNames')->andreturn(array(
				'minifyCssEnabled',
				'cssPrependUrlEnabled',
				'cssPrependUrl'
			));
			$settingsModelMock->shouldReceive('getAttribute')->with('minifyCssEnabled')->andreturn(false);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrlEnabled')->andreturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrl')->andreturn('');

			return $settingsModelMock;
		});

		minimee()->service->settings = minimee()->makeSettingsModel();
		minimee()->service->type = MinimeeType::Css;

		$asset = minimee()->makeLocalAssetModel();

		$minifyAsset = $this->getMethod(minimee()->service, 'minifyAsset');
		$this->assertEquals($assetContentsWrite, $minifyAsset->invokeArgs(minimee()->service, array($asset)));
	}

	public function testMinifyAssetWithCssWhenMinifyCssEnabledIsFalse()
	{
		$assetContents = file_get_contents(__DIR__ . '/../assets/css/style.1.css');

		minimee()->extend('makeLocalAssetModel', function() use ($assetContents) {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(true);
			$localAssetModelMock->shouldReceive('getAttribute')->with('filenameUrl')->andReturn('');
			$localAssetModelMock->shouldReceive('getContents')->andReturn($assetContents);

			return $localAssetModelMock;
		});

		minimee()->extend('makeSettingsModel', function() use ($assetContents) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('validate')->andReturn(true);
			$settingsModelMock->shouldReceive('attributeNames')->andreturn(array(
				'minifyCssEnabled',
				'cssPrependUrlEnabled',
				'cssPrependUrl'
			));
			$settingsModelMock->shouldReceive('getAttribute')->with('minifyCssEnabled')->andreturn(false);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrlEnabled')->andreturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrl')->andreturn('');

			return $settingsModelMock;
		});

		minimee()->service->settings = minimee()->makeSettingsModel();
		minimee()->service->type = MinimeeType::Css;

		$asset = minimee()->makeLocalAssetModel();

		$minifyAsset = $this->getMethod(minimee()->service, 'minifyAsset');
		$this->assertEquals($assetContents, $minifyAsset->invokeArgs(minimee()->service, array($asset)));
	}

	public function testMinifyAssetWithCssRewritesUrlWhenCssPrependUrlEnabledIsFalse() {}

	public function testMinifyAssetWithCssRewritesUrlWhenCssPrependUrlEnabledIsTrueAndCssPrependUrlIsNonEmpty() {
		$assetContents = file_get_contents(__DIR__ . '/../assets/css/style.2.css');
		$assetContentsWrite = file_get_contents(__DIR__ . '/../assets/css/style.2.prepend.min.css');

		minimee()->extend('makeLocalAssetModel', function() use ($assetContents) {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(true);
			$localAssetModelMock->shouldReceive('getAttribute')->with('filenameUrl')->andReturn('http://craft.dev/assets/css/style.2.css');
			$localAssetModelMock->shouldReceive('getContents')->andReturn($assetContents);

			return $localAssetModelMock;
		});

		minimee()->extend('makeSettingsModel', function() use ($assetContents) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('validate')->andReturn(true);
			$settingsModelMock->shouldReceive('attributeNames')->andreturn(array(
				'minifyCssEnabled',
				'cssPrependUrlEnabled',
				'cssPrependUrl'
			));
			$settingsModelMock->shouldReceive('getAttribute')->with('minifyCssEnabled')->andreturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrlEnabled')->andreturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrl')->andreturn('http://craft2.dev/assets/css/');

			return $settingsModelMock;
		});

		minimee()->service->settings = minimee()->makeSettingsModel();
		minimee()->service->type = MinimeeType::Css;

		$asset = minimee()->makeLocalAssetModel();

		$minifyAsset = $this->getMethod(minimee()->service, 'minifyAsset');
		$this->assertEquals($assetContentsWrite, $minifyAsset->invokeArgs(minimee()->service, array($asset)));
	}

	public function testMinifyAssetWithJsWhenMinifyJsEnabledIsFalse()
	{
		$assetContents = file_get_contents(__DIR__ . '/../assets/js/script.1.js');

		minimee()->extend('makeLocalAssetModel', function() use ($assetContents) {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(true);
			$localAssetModelMock->shouldReceive('getAttribute')->with('filenameUrl')->andReturn('');
			$localAssetModelMock->shouldReceive('getContents')->andReturn($assetContents);

			return $localAssetModelMock;
		});

		minimee()->extend('makeSettingsModel', function() use ($assetContents) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('validate')->andReturn(true);
			$settingsModelMock->shouldReceive('attributeNames')->andreturn(array(
				'minifyJsEnabled'
			));
			$settingsModelMock->shouldReceive('getAttribute')->with('minifyJsEnabled')->andreturn(false);

			return $settingsModelMock;
		});

		minimee()->service->settings = minimee()->makeSettingsModel();
		minimee()->service->type = MinimeeType::Js;

		$asset = minimee()->makeLocalAssetModel();

		$minifyAsset = $this->getMethod(minimee()->service, 'minifyAsset');
		$this->assertEquals($assetContents, $minifyAsset->invokeArgs(minimee()->service, array($asset)));
	}

	public function testMinifyAssetWithJsWhenMinifyJsEnabledIsTrue()
	{
		$assetContents = file_get_contents(__DIR__ . '/../assets/js/script.1.js');
		$assetMinifiedContents = file_get_contents(__DIR__ . '/../assets/js/script.1.min.js');

		minimee()->extend('makeLocalAssetModel', function() use ($assetContents) {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(true);
			$localAssetModelMock->shouldReceive('getAttribute')->with('filenameUrl')->andReturn('');
			$contents = $assetContents;
			$localAssetModelMock->shouldReceive('getContents')->andReturn($contents);

			return $localAssetModelMock;
		});

		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('validate')->andReturn(true);
			$settingsModelMock->shouldReceive('attributeNames')->andreturn(array(
				'minifyJsEnabled'
			));
			$settingsModelMock->shouldReceive('getAttribute')->with('minifyJsEnabled')->andreturn(true);

			return $settingsModelMock;
		});

		minimee()->service->settings = minimee()->makeSettingsModel();
		minimee()->service->type = MinimeeType::Js;

		$asset = minimee()->makeLocalAssetModel();

		$minifyAsset = $this->getMethod(minimee()->service, 'minifyAsset');
		$this->assertEquals($assetMinifiedContents, $minifyAsset->invokeArgs(minimee()->service, array($asset)));
	}

	public function testMinifyAssetWithCssWhenMinifyCssEnabledIsTrue()
	{
		$assetContents = file_get_contents(__DIR__ . '/../assets/css/style.1.css');
		$assetMinifiedContents = file_get_contents(__DIR__ . '/../assets/css/style.1.min.css');

		minimee()->extend('makeLocalAssetModel', function() use ($assetContents) {
			$localAssetModelMock = m::mock('Craft\Minimee_LocalAssetModel')->makePartial();
			$localAssetModelMock->shouldReceive('exists')->andReturn(true);
			$localAssetModelMock->shouldReceive('getAttribute')->with('filenameUrl')->andReturn('');
			$contents = $assetContents;
			$localAssetModelMock->shouldReceive('getContents')->andReturn($contents);

			return $localAssetModelMock;
		});

		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('validate')->andReturn(true);
			$settingsModelMock->shouldReceive('attributeNames')->andreturn(array(
				'minifyCssEnabled',
				'cssPrependUrlEnabled',
				'cssPrependUrl'
			));
			$settingsModelMock->shouldReceive('getAttribute')->with('minifyCssEnabled')->andreturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrlEnabled')->andreturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('cssPrependUrl')->andreturn('');

			return $settingsModelMock;
		});

		minimee()->service->settings = minimee()->makeSettingsModel();
		minimee()->service->type = MinimeeType::Css;

		$asset = minimee()->makeLocalAssetModel();

		$minifyAsset = $this->getMethod(minimee()->service, 'minifyAsset');
		$this->assertEquals($assetMinifiedContents, $minifyAsset->invokeArgs(minimee()->service, array($asset)));
	}

	public function testFlightcheckPasses()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('validate')->andReturn(true);
			$settingsModelMock->shouldReceive('getAttribute')->with('enabled')->andreturn(true);

			return $settingsModelMock;
		});

		minimee()->service->settings = minimee()->makeSettingsModel();
		minimee()->service->type = MinimeeType::Css;
		minimee()->service->assets = array(
			'/assets/css/normalise.css',
			'/assets/css/style.css'
		);

		$flightcheck = $this->getMethod(minimee()->service, 'flightcheck');

		// not yet ready to assert
		// $this->assertTrue($flightcheck->invoke(minimee()->service));

	}

	public function testMakeCacheFilenameWhenUseResourceCacheReturnsFalse()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('useResourceCache')->andReturn(false);

			return $settingsModelMock;
		});

		minimee()->service->cacheBase = 'base';
		minimee()->service->cacheTimestamp = '12345678';
		minimee()->service->type = MinimeeType::Css;

		$makeCacheFilename = $this->getMethod(minimee()->service, 'makeCacheFilename');
		$this->assertEquals(sha1('base') . '.12345678.css', $makeCacheFilename->invoke(minimee()->service));
	}

	public function testMakeCacheFilenameWhenUseResourceCacheReturnsTrue()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel');
			$settingsModelMock->shouldReceive('useResourceCache')->andReturn(true);

			return $settingsModelMock;
		});

		minimee()->service->cacheBase = 'base';
		minimee()->service->cacheTimestamp = '12345678';
		minimee()->service->type = MinimeeType::Css;

		$makeCacheFilename = $this->getMethod(minimee()->service, 'makeCacheFilename');
		$this->assertEquals(sha1('base') . '.css', $makeCacheFilename->invoke(minimee()->service));
	}

	public function testMakeUrlToCacheFilenameWhenUseResourceCacheReturnsFalse()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('useResourceCache')->andReturn(false);
			$settingsModelMock->shouldReceive('getCacheUrl')->andReturn('http://craft.dev/cache/');

			return $settingsModelMock;
		});

		minimee()->service->cacheBase = 'base';
		minimee()->service->cacheTimestamp = '12345678';
		minimee()->service->type = MinimeeType::Css;

		$assertEquals = 'http://craft.dev/cache/' . sha1('base') . '.12345678.css';

		$makeUrlToCacheFilename = $this->getMethod(minimee()->service, 'makeUrlToCacheFilename');
		$this->assertEquals($assertEquals, $makeUrlToCacheFilename->invoke(minimee()->service));
	}

	public function testMakeReturnCallsMakePathToCacheFilenameWhenReturnTypeIsUrl()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('useResourceCache')->andReturn(false);
			$settingsModelMock->shouldReceive('getCacheUrl')->andReturn('http://craft.dev/cache/');

			return $settingsModelMock;
		});

		minimee()->service->cacheBase = 'base';
		minimee()->service->cacheTimestamp = '12345678';
		minimee()->service->type = MinimeeType::Css;

		$assertEquals = 'http://craft.dev/cache/' . sha1('base') . '.12345678.css';

		$makeReturn = $this->getMethod(minimee()->service, 'makeReturn');
		$this->assertEquals($assertEquals, $makeReturn->invoke(minimee()->service));
	}

	public function testMakePathToCacheFilenameWhenUseResourceCacheReturnsFalse()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('useResourceCache')->andReturn(false);
			$settingsModelMock->shouldReceive('getCachePath')->andReturn('/usr/var/www/html/cache/');

			return $settingsModelMock;
		});

		minimee()->service->cacheBase = 'base';
		minimee()->service->cacheTimestamp = '12345678';
		minimee()->service->type = MinimeeType::Css;

		$hashOfCacheBase = sha1('base');

		$makePathToCacheFilename = $this->getMethod(minimee()->service, 'makePathToCacheFilename');
		$this->assertEquals('/usr/var/www/html/cache/' . $hashOfCacheBase . '.12345678.css', $makePathToCacheFilename->invoke(minimee()->service));
	}

	public function testMakePathToCacheFilenameWhenUseResourceCacheReturnsTrue()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('useResourceCache')->andReturn(true);

			return $settingsModelMock;
		});

		minimee()->service->cacheBase = 'base';
		minimee()->service->cacheTimestamp = '12345678';
		minimee()->service->type = MinimeeType::Css;

		$assertEquals = craft()->path->getStoragePath() . MinimeeService::ResourceTrigger . '/' . sha1('base') . '.css';

		$makePathToCacheFilename = $this->getMethod(minimee()->service, 'makePathToCacheFilename');
		$this->assertEquals($assertEquals, $makePathToCacheFilename->invoke(minimee()->service));
	}

	public function testMakePathToHashOfCacheBaseWhenUseResourceCacheReturnsFalse()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('useResourceCache')->andReturn(false);
			$settingsModelMock->shouldReceive('getCachePath')->andReturn('/usr/var/www/html/cache/');

			return $settingsModelMock;
		});

		minimee()->service->cacheBase = 'base';
		$hashOfCacheBase = sha1('base');

		$makePathToHashOfCacheBase = $this->getMethod(minimee()->service, 'makePathToHashOfCacheBase');
		$this->assertEquals('/usr/var/www/html/cache/' . $hashOfCacheBase, $makePathToHashOfCacheBase->invoke(minimee()->service));
	}

	public function testMakePathToHashOfCacheBaseWhenUseResourceCacheReturnsTrue()
	{
		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('useResourceCache')->andReturn(true);

			return $settingsModelMock;
		});

		minimee()->service->cacheBase = 'base';
		$hashOfCacheBase = sha1('base');

//		$resourceUrlBase = UrlHelper::getResourceUrl(MinimeeService::ResourceTrigger);
		$resourcePathBase = craft()->path->getStoragePath() . MinimeeService::ResourceTrigger . '/';

		$assertEquals = $resourcePathBase . $hashOfCacheBase;

		$makePathToHashOfCacheBase = $this->getMethod(minimee()->service, 'makePathToHashOfCacheBase');
		$this->assertEquals($assertEquals, $makePathToHashOfCacheBase->invoke(minimee()->service));
	}

	public function testMakeHashOfCacheBase()
	{
		minimee()->service->cacheBase = 'asdf1234';

		$makeHashOfCacheBase = $this->getMethod(minimee()->service, 'makeHashOfCacheBase');
		$hashOfCacheBase = $makeHashOfCacheBase->invoke(minimee()->service);

		$this->assertEquals(sha1(minimee()->service->cacheBase), $hashOfCacheBase);
	}

	public function testSetAssetsWhenLocalCss()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$minimeeService = $setAssets->invokeArgs(minimee()->service, array(
			'/assets/css/style.css'
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_LocalAssetModel', $getAssets[0]);
		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
	}

	public function testSetAssetsWhenLocalJs()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$minimeeService = $setAssets->invokeArgs(minimee()->service, array(
			'/assets/js/app.js'
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_LocalAssetModel', $getAssets[0]);
		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
	}

	public function testSetAssetsWhenRemoteCss()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$minimeeService = $setAssets->invokeArgs(minimee()->service, array(
			'http://craft.dev/assets/css/style.css'
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_RemoteAssetModel', $getAssets[0]);
		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
	}

	public function testSetAssetsWhenRemoteJs()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$minimeeService = $setAssets->invokeArgs(minimee()->service, array(
			'http://craft.dev/assets/js/app.js'
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_RemoteAssetModel', $getAssets[0]);
		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
	}

	public function testSetAssetsWhenMixedLocalAndRemoteCss()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$minimeeService = $setAssets->invokeArgs(minimee()->service, array(
			array(
				'/assets/js/jquery.js',
				'http://craft.dev/assets/js/app.js'
			)
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_LocalAssetModel', $getAssets[0]);
		$this->assertInstanceOf('\Craft\Minimee_RemoteAssetModel', $getAssets[1]);
		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
	}

	public function testSetAssetsWhenMixedLocalAndRemoteJs()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$minimeeService = $setAssets->invokeArgs(minimee()->service, array(
			array(
				'/assets/css/normalize.css',
				'http://craft.dev/assets/css/style.css'
			)
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_LocalAssetModel', $getAssets[0]);
		$this->assertInstanceOf('\Craft\Minimee_RemoteAssetModel', $getAssets[1]);
		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
	}

	public function testSetTypeAllEnums()
	{
		$setType = $this->getMethod(minimee()->service, 'setType');
		$setType->invokeArgs(minimee()->service, array(MinimeeType::Css));
		$this->assertSame(MinimeeType::Css, minimee()->service->type);

		$minimeeService = $setType->invokeArgs(minimee()->service, array(MinimeeType::Js));

		$this->assertSame(MinimeeType::Js, minimee()->service->type);
		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
	}

	/**
     * @expectedException Exception
     */
    public function testSetTypeInvalid()
    {
		$setType = $this->getMethod(minimee()->service, 'setType');
		$setType->invokeArgs(minimee()->service, array('CSS'));
    }

    public function dataProviderIsEnabledReturnsTrue()
    {
		// the first element is the type we check;
		// the second element is the other type, which should have no impact
    	return [
    		[true, true],
    		[true, false]
    	];
    }

    public function dataProviderIsEnabledReturnsFalse()
    {
		// the first element is the type we check;
		// the second element is the other type, which should have no impact
    	return [
    		[false, true],
    		[false, false]
    	];
    }

	/**
	 * @dataProvider dataProviderIsEnabledReturnsTrue
	 */
	public function testIsCombineEnabledReturnsTrueWhenTypeIsCss($combineCssEnabled, $combineJsEnabled)
	{
		minimee()->service->type = 'css';

		minimee()->extend('makeSettingsModel', function() use ($combineCssEnabled, $combineJsEnabled) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('getAttribute')->with('combineCssEnabled')->andReturn($combineCssEnabled);
			$settingsModelMock->shouldReceive('getAttribute')->with('combineJsEnabled')->andReturn($combineJsEnabled);

			return $settingsModelMock;
		});

		$isCombineEnabled = $this->getMethod(minimee()->service, 'isCombineEnabled');

		$this->assertTrue($isCombineEnabled->invoke(minimee()->service));
	}


	/**
	 * @dataProvider dataProviderIsEnabledReturnsFalse
	 */
	public function testIsCombineEnabledReturnsFalseWhenTypeIsCss($combineCssEnabled, $combineJsEnabled)
	{
		minimee()->service->type = 'css';

		minimee()->extend('makeSettingsModel', function() use ($combineCssEnabled, $combineJsEnabled) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('getAttribute')->with('combineCssEnabled')->andReturn($combineCssEnabled);
			$settingsModelMock->shouldReceive('getAttribute')->with('combineJsEnabled')->andReturn($combineJsEnabled);

			return $settingsModelMock;
		});

		$isCombineEnabled = $this->getMethod(minimee()->service, 'isCombineEnabled');

		$this->assertFalse($isCombineEnabled->invoke(minimee()->service));
	}

	/**
	 * @dataProvider dataProviderIsEnabledReturnsTrue
	 */
	public function testIsCombineEnabledReturnsTrueWhenTypeIsJs($combineJsEnabled, $combineCssEnabled)
	{
		minimee()->service->type = 'js';

		minimee()->extend('makeSettingsModel', function() use ($combineCssEnabled, $combineJsEnabled) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('getAttribute')->with('combineCssEnabled')->andReturn($combineCssEnabled);
			$settingsModelMock->shouldReceive('getAttribute')->with('combineJsEnabled')->andReturn($combineJsEnabled);

			return $settingsModelMock;
		});

		$isCombineEnabled = $this->getMethod(minimee()->service, 'isCombineEnabled');

		$this->assertTrue($isCombineEnabled->invoke(minimee()->service));
	}

	/**
	 * @dataProvider dataProviderIsEnabledReturnsFalse
	 */
	public function testIsCombineEnabledReturnsFalseWhenTypeIsJs($combineJsEnabled, $combineCssEnabled)
	{
		minimee()->service->type = 'js';

		minimee()->extend('makeSettingsModel', function() use ($combineCssEnabled, $combineJsEnabled) {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('getAttribute')->with('combineCssEnabled')->andReturn($combineCssEnabled);
			$settingsModelMock->shouldReceive('getAttribute')->with('combineJsEnabled')->andReturn($combineJsEnabled);

			return $settingsModelMock;
		});

		$isCombineEnabled = $this->getMethod(minimee()->service, 'isCombineEnabled');

		$this->assertFalse($isCombineEnabled->invoke(minimee()->service));
	}


	public function testSetMaxCacheTimestampAlwaysSetsMax()
	{
		$setMaxCacheTimestamp = $this->getMethod(minimee()->service, 'setMaxCacheTimestamp');

		$dt = new DateTime('now');
		$nowTimestamp = $dt->getTimestamp();

		$setMaxCacheTimestamp->invokeArgs(minimee()->service, array($dt));
		$this->assertEquals($nowTimestamp, minimee()->service->cacheTimestamp);

		// reduce by a day
		$dt->modify("-1 day");
		$yesterdayTimestamp = $dt->getTimestamp();

		$setMaxCacheTimestamp->invokeArgs(minimee()->service, array($dt));
		$this->assertEquals($nowTimestamp, minimee()->service->cacheTimestamp);

		// increase by 2 days
		$dt->modify("+2 day");
		$tomorrowTimestamp = $dt->getTimestamp();

		$setMaxCacheTimestamp->invokeArgs(minimee()->service, array($dt));
		$this->assertEquals($tomorrowTimestamp, minimee()->service->cacheTimestamp);

		// test that setting it to the same value has no ill effect
		$setMaxCacheTimestamp->invokeArgs(minimee()->service, array($dt));
		$this->assertEquals($tomorrowTimestamp, minimee()->service->cacheTimestamp);
	}

	public function testGetCacheTimestampWhenZeroReturnsPaddedZeros()
	{
		$getCacheTimestamp = $this->getMethod(minimee()->service, 'getCacheTimestamp');
		$this->assertEquals(MinimeeService::TimestampZero, $getCacheTimestamp->invoke(minimee()->service));
	}

	public function testMakeTagsByTypePassingCssString()
	{
		$css = 'http://craft.dev/cache/hash.timestamp.css';

		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('getAttribute')->with('cssReturnTemplate')->andReturn('<link rel="stylesheet" href="%s"/>');

			return $settingsModelMock;
		});

		$cssReturnTemplate = minimee()->service->settings->cssReturnTemplate;

		$rendered = sprintf($cssReturnTemplate, $css);
		$this->assertEquals($rendered, minimee()->service->makeTagsByType('css', $css));
	}

	public function testMakeTagsByTypePassingCssArray()
	{
		$cssArray = array(
			'http://craft.dev/cache/hash1.timestamp.css',
			'http://craft.dev/cache/hash2.timestamp.css'
		);

		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('getAttribute')->with('cssReturnTemplate')->andReturn('<link rel="stylesheet" href="%s"/>');

			return $settingsModelMock;
		});

		$cssReturnTemplate = minimee()->service->settings->cssReturnTemplate;

		$rendered = '';
		foreach($cssArray as $css)
		{
			$rendered .= sprintf($cssReturnTemplate, $css);
		}

		$this->assertEquals($rendered, minimee()->service->makeTagsByType('css', $cssArray));
	}

	public function testMakeTagsByTypePassingJsString()
	{
		$js = 'http://craft.dev/cache/hash.timestamp.js';

		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('getAttribute')->with('jsReturnTemplate')->andReturn('<script src="%s"></script>');

			return $settingsModelMock;
		});

		$jsReturnTemplate = minimee()->service->settings->jsReturnTemplate;

		$rendered = sprintf($jsReturnTemplate, $js);
		$this->assertEquals($rendered, minimee()->service->makeTagsByType('js', $js));
	}

	public function testMakeTagsByTypePassingJsArray()
	{
		$jsArray = array(
			'http://craft.dev/cache/hash1.timestamp.js',
			'http://craft.dev/cache/hash2.timestamp.js'
		);

		minimee()->extend('makeSettingsModel', function() {
			$settingsModelMock = m::mock('Craft\Minimee_SettingsModel')->makePartial();
			$settingsModelMock->shouldReceive('getAttribute')->with('jsReturnTemplate')->andReturn('<script src="%s"></script>');

			return $settingsModelMock;
		});

		$jsReturnTemplate = minimee()->service->settings->jsReturnTemplate;

		$rendered = '';
		foreach($jsArray as $js)
		{
			$rendered .= sprintf($jsReturnTemplate, $js);
		}

		$this->assertEquals($rendered, minimee()->service->makeTagsByType('js', $jsArray));
	}

	public function testReset()
	{
		minimee()->service->assets = array('/asset/css/style.css');
		minimee()->service->type = MinimeeType::Css;
		minimee()->service->settings = new Minimee_SettingsModel(array(
			'enabled' => true));
		minimee()->service->cacheBase = 'asset.css.style.css';
		minimee()->service->cacheTimestamp = new DateTime('now');

		$reset = $this->getMethod(minimee()->service, 'reset');
		$minimeeService = $reset->invoke(minimee()->service);

		$this->assertInstanceOf('\Craft\MinimeeService', $minimeeService);
		$this->assertSame(array(), minimee()->service->assets);
		$this->assertInstanceOf('\Craft\Minimee_SettingsModel', minimee()->service->settings);
		$this->assertSame('', minimee()->service->type);
		$this->assertSame('', minimee()->service->cacheBase);
		$this->assertSame(MinimeeService::TimestampZero, minimee()->service->cacheTimestamp);
	}

	public function dataProviderInvalidUrls()
	{
		return [
			['craft.dev'],
			['/craft.dev']
		];
	}

	/**
	 * @dataProvider dataProviderInvalidUrls
	 */
	public function testIsUrlInvalid($url)
	{
		$isUrl = $this->getMethod(minimee()->service, 'isUrl');
		$this->assertFalse($isUrl->invokeArgs(minimee()->service, array($url)));
	}

	public function dataProviderValidUrls()
	{
		return [
			['http://craft.dev'],
			['https://craft.dev'],
			['//craft.dev']
		];
	}

	/**
	 * @dataProvider dataProviderValidUrls
	 */
	public function testIsUrlValid($url)
	{
		$isUrl = $this->getMethod(minimee()->service, 'isUrl');
		$this->assertTrue($isUrl->invokeArgs(minimee()->service, array($url)));
	}

	protected function _autoload()
	{
		// These are usually automatically loaded by Craft
		Craft::import('plugins.minimee.MinimeePlugin');
		Craft::import('plugins.minimee.services.MinimeeService');

		// This is loaded via MinimeePlugin::init()
		Craft::import('plugins.minimee.enums.MinimeeType');
	}
}

<?php
namespace Craft;

use \Mockery as m;
use \SelvinOrtiz\Zit\Zit;

class MinimeeTwigExtensionTest extends MinimeeBaseTest
{
	public function setUp()
	{
		$this->_autoload();

		minimee()->stash('plugin', new MinimeePlugin);
		minimee()->stash('service', new MinimeeService);
	}

	public function testPregMatchAssetsByTypeAsCssSingleDoubleQuotes()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link href="/assets/css/style.css">
EOF;
		$this->assertEquals(array('/assets/css/style.css'), $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssSingleDoubleQuotesWithTrailingSlash()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link href="/assets/css/style.css" />
EOF;
		$this->assertEquals(array('/assets/css/style.css'), $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssSingleSingleQuotes()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link href='/assets/css/style.css'>
EOF;
		$this->assertEquals(array('/assets/css/style.css'), $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssSingleSingleQuotesWithTrailingSlash()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link href='/assets/css/style.css' />
EOF;
		$this->assertEquals(array('/assets/css/style.css'), $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssMultipleDoubleQuotes()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link href="/assets/css/style.1.css">
<link href="/assets/css/style.2.css">
EOF;

		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);
		$this->assertEquals($equals, $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssMultipleDoubleQuotesWithTrailingSlash()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link href="/assets/css/style.1.css" />
<link href="/assets/css/style.2.css" />
EOF;

		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);
		$this->assertEquals($equals, $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssMultipleSingleQuotes()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link href='/assets/css/style.1.css'>
<link href='/assets/css/style.2.css'>
EOF;

		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);
		$this->assertEquals($equals, $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssMultipleSingleQuotesWithTrailingSlash()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link href='/assets/css/style.1.css' />
<link href='/assets/css/style.2.css' />
EOF;

		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);
		$this->assertEquals($equals, $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssMultipleWithLinkBreaksDoubleQuotes()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link
rel="stylesheet" href="/assets/css/style.1.css"><link
rel="stylesheet" href="/assets/css/style.2.css">
EOF;
		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$this->assertEquals($equals, $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssMultipleWithLinkBreaksDoubleQuotesWithTrailingSlash()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link
rel="stylesheet" href="/assets/css/style.1.css" /><link
rel="stylesheet" href="/assets/css/style.2.css" />
EOF;
		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$this->assertEquals($equals, $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssMultipleWithLinkBreaksSingleQuotes()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link
rel="stylesheet" href='/assets/css/style.1.css'><link
rel="stylesheet" href='/assets/css/style.2.css'>
EOF;
		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$this->assertEquals($equals, $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	public function testPregMatchAssetsByTypeAsCssMultipleWithLinkBreaksSingleQuotesWithTrailingSlash()
	{
		$twigExtension = new MinimeeTwigExtension();

		$html =<<<EOF
<link
rel="stylesheet" href='/assets/css/style.1.css' /><link
rel="stylesheet" href='/assets/css/style.2.css' />
EOF;
		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$this->assertEquals($equals, $twigExtension->testPregMatchAssetsByType(MinimeeType::Css, $html));
	}

	protected function _autoload()
	{
		// These are usually automatically loaded by Craft
		Craft::import('plugins.minimee.MinimeePlugin');

		// This is loaded via MinimeePlugin::init()
		Craft::import('plugins.minimee.enums.MinimeeType');
	}
}

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
		$html =<<<EOF
<link href="/assets/css/style.css">
EOF;

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals(array('/assets/css/style.css'), $result);
	}

	public function testPregMatchAssetsByTypeAsCssSingleDoubleQuotesWithTrailingSlash()
	{
		$html =<<<EOF
<link href="/assets/css/style.css" />
EOF;
		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals(array('/assets/css/style.css'), $result);
	}

	public function testPregMatchAssetsByTypeAsCssSingleSingleQuotes()
	{
		$html =<<<EOF
<link href='/assets/css/style.css'>
EOF;

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals(array('/assets/css/style.css'), $result);
	}

	public function testPregMatchAssetsByTypeAsCssSingleSingleQuotesWithTrailingSlash()
	{
		$html =<<<EOF
<link href='/assets/css/style.css' />
EOF;

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals(array('/assets/css/style.css'), $result);
	}

	public function testPregMatchAssetsByTypeAsCssMultipleDoubleQuotes()
	{
		$html =<<<EOF
<link href="/assets/css/style.1.css">
<link href="/assets/css/style.2.css">
EOF;

		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals($equals, $result);
	}

	public function testPregMatchAssetsByTypeAsCssMultipleDoubleQuotesWithTrailingSlash()
	{
		$html =<<<EOF
<link href="/assets/css/style.1.css" />
<link href="/assets/css/style.2.css" />
EOF;

		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals($equals, $result);
	}

	public function testPregMatchAssetsByTypeAsCssMultipleSingleQuotes()
	{
		$html =<<<EOF
<link href='/assets/css/style.1.css'>
<link href='/assets/css/style.2.css'>
EOF;

		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals($equals, $result);
	}

	public function testPregMatchAssetsByTypeAsCssMultipleSingleQuotesWithTrailingSlash()
	{
		$html =<<<EOF
<link href='/assets/css/style.1.css' />
<link href='/assets/css/style.2.css' />
EOF;

		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals($equals, $result);
	}

	public function testPregMatchAssetsByTypeAsCssMultipleWithLinkBreaksDoubleQuotes()
	{
		$html =<<<EOF
<link
rel="stylesheet" href="/assets/css/style.1.css"><link
rel="stylesheet" href="/assets/css/style.2.css">
EOF;
		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals($equals, $result);
	}

	public function testPregMatchAssetsByTypeAsCssMultipleWithLinkBreaksDoubleQuotesWithTrailingSlash()
	{
		$html =<<<EOF
<link
rel="stylesheet" href="/assets/css/style.1.css" /><link
rel="stylesheet" href="/assets/css/style.2.css" />
EOF;
		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals($equals, $result);
	}

	public function testPregMatchAssetsByTypeAsCssMultipleWithLinkBreaksSingleQuotes()
	{
		$html =<<<EOF
<link
rel="stylesheet" href='/assets/css/style.1.css'><link
rel="stylesheet" href='/assets/css/style.2.css'>
EOF;
		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals($equals, $result);
	}

	public function testPregMatchAssetsByTypeAsCssMultipleWithLinkBreaksSingleQuotesWithTrailingSlash()
	{
		$html =<<<EOF
<link
rel="stylesheet" href='/assets/css/style.1.css' /><link
rel="stylesheet" href='/assets/css/style.2.css' />
EOF;
		$equals = array(
			'/assets/css/style.1.css',
			'/assets/css/style.2.css'
		);

		$result = $this->invokePregMatchAssetsByType(MinimeeType::Css, $html);
		$this->assertEquals($equals, $result);
	}

	protected function invokePregMatchAssetsByType($type, $html)
	{
		$twigExtension = new MinimeeTwigExtension();
		$pregMatchAssetsByType = $this->getMethod($twigExtension, 'pregMatchAssetsByType');
		return $pregMatchAssetsByType->invokeArgs($twigExtension, array(MinimeeType::Css, $html));
	}

	protected function _autoload()
	{
		// These are usually automatically loaded by Craft
		Craft::import('plugins.minimee.MinimeePlugin');

		// This is loaded via MinimeePlugin::init()
		Craft::import('plugins.minimee.enums.MinimeeType');
	}
}

<?php
namespace Craft;

/**
 * Minimee by John D Wells
 *
 * @package   Minimee
 * @author    John D Wells
 * @copyright Copyright (c) 2014, John D Wells
 * @link      http://johndwells.com
 */

/**
 * 
 */
class Minimee_SettingsModel extends BaseModel
{
	/**
	 *
	 * @return string
	 */
	public function __toString()
	{
		return (bool) $this->enabled;
	}

	// --------------------

	/**
	 * @return Array
	 */
	public function defineAttributes()
	{
		return array(
			'cacheFolder'       => AttributeType::String,
			'enabled'           => array(AttributeType::Bool,'default' => true),
			'filesystemPath'    => AttributeType::String
		);
	}

	public function forceTrailingSlash($string)
	{
		return rtrim($string, '/') . '/';
	}

	public function getCachePath()
	{
		if ($this->cacheFolder != '')
		{
			$cachePath = $this->filesystemPath . $this->cacheFolder;
		}
		else
		{
			$cachePath = craft()->path->getStoragePath() . 'minimee/';
		}

		return $this->forceTrailingSlash($cachePath);
	}
	
	public function getCacheUrl()
	{
		if ($this->cacheFolder != '')
		{
			$cacheUrl = $this->baseUrl . $this->cacheFolder;
		}
		else
		{
			$cacheUrl = UrlHelper::getResourceUrl('minimee');
		}

		return $this->forceTrailingSlash($cacheUrl);
	}
	
	public function getBaseUrl()
	{
		return $this->forceTrailingSlash(craft()->getSiteUrl());
	}

	public function getRemoteMode()
	{
		return 'fgc';
	}

	public function getAttribute($name)
	{
		if($name == 'filesystemPath')
		{
			$value = parent::getAttribute($name);

			$filesystemPath = ($value) ? craft()->config->parseEnvironmentString($value) : $_SERVER['DOCUMENT_ROOT'];

			return $this->forceTrailingSlash($filesystemPath);
		}

		return parent::getAttribute($name);
	}

	/**
	 * @return Bool whether cache folder exists
	 */
	public function exists()
	{
		return IOHelper::folderExists($this->cachePath);
	}

	// --------------------

}
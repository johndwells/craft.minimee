<?php namespace Craft;

/**
 * Minimee by John D Wells
 *
 * @author     	John D Wells <http://johndwells.com>
 * @package    	Minimee
 * @since		Craft 1.3
 * @copyright 	Copyright (c) 2014, John D Wells
 * @license 	http://opensource.org/licenses/mit-license.php MIT License
 * @link       	http://github.com/johndwells/Minimee-Craft
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

	public function validate($attributes = null, $clearErrors = true)
	{
		$this->validateCachePathAndUrl();

		return parent::validate($attributes, false);
	}

	public function validateCachePathAndUrl()
	{
		$cachePath = parent::getAttribute('cachePath');
		$cacheUrl = parent::getAttribute('cacheUrl');

		$cachePathEmpty = !! $cachePath;
		$cacheUrlEmpty = !! $cacheUrl;

		if($cachePathEmpty != $cacheUrlEmpty)
		{
			$choose = ($cacheUrlEmpty) ? 'cachePath' : 'cacheUrl';
			$this->addError($choose, Craft::t('cachePath and cacheUrl must both either be empty or non-empty.'));
		}
	}

	/**
	 * @return Array
	 */
	public function defineAttributes()
	{
		return array(
			'cachePath'       	=> AttributeType::String,
			'cacheUrl'       	=> AttributeType::String,
			'enabled'           => array(AttributeType::Enum, 'values' => "on"),
			'filesystemPath'    => array(AttributeType::String)
		);
	}

	public function forceTrailingSlash($string)
	{
		return rtrim($string, '/') . '/';
	}

	public function getFilesystemPath()
	{
		$value = parent::getAttribute('filesystemPath');

		$filesystemPath = ($value) ? craft()->config->parseEnvironmentString($value) : $_SERVER['DOCUMENT_ROOT'];

		return $this->forceTrailingSlash($filesystemPath);
	}

	public function getCachePath()
	{
		$value = parent::getAttribute('cachePath');

		$cachePath = ($value) ? craft()->config->parseEnvironmentString($value) : craft()->path->getStoragePath() . 'minimee/';

		return $this->forceTrailingSlash($cachePath);
	}
	
	public function getCacheUrl()
	{
		$value = parent::getAttribute('cacheUrl');

		$cacheUrl = ($value) ? craft()->config->parseEnvironmentString($value) : UrlHelper::getResourceUrl('minimee');

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
		switch($name) :

			case('cachePath') :
				return $this->getCachePath();			
			break;

			case('cacheUrl') :
				return $this->getCacheUrl();			
			break;

			case('filesystemPath') :
				return $this->getFilesystemPath();			
			break;

		endswitch;

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
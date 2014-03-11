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
	 * @return string
	 */
	public function __toString()
	{
		return ($this->enabled) ? '1' : '0';
	}

	/**
	 * Add custom validation rules to routine.
	 *
	 * @param Array $attributes
	 * @param Bool $clearErrors
	 * @return Bool
	 */
	public function validate($attributes = null, $clearErrors = true)
	{
		$this->validateCachePathAndUrl();

		return parent::validate($attributes, false);
	}

	/**
	 * Validate that cachePath and cacheUrl are both empty or non-empty.
	 *
	 * @return Bool
	 */
	public function validateCachePathAndUrl()
	{
		$cachePath = parent::getAttribute('cachePath');
		$cacheUrl = parent::getAttribute('cacheUrl');

		$cachePathEmpty = ! $cachePath;
		$cacheUrlEmpty = ! $cacheUrl;

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

	/**
	 * @param String $string
	 * @return String
	 */
	public function forceTrailingSlash($string)
	{
		return rtrim($string, '/') . '/';
	}

	/**
	 * @return String
	 */
	public function getFilesystemPath()
	{
		$value = parent::getAttribute('filesystemPath');

		$filesystemPath = ($value) ? craft()->config->parseEnvironmentString($value) : $_SERVER['DOCUMENT_ROOT'];

		return $this->forceTrailingSlash($filesystemPath);
	}

	/**
	 * @return String
	 */
	public function getCachePath()
	{
		$value = parent::getAttribute('cachePath');

		$cachePath = ($value) ? craft()->config->parseEnvironmentString($value) : craft()->path->getStoragePath() . 'minimee/';

		return $this->forceTrailingSlash($cachePath);
	}
	
	/**
	 * @return String
	 */
	public function getCacheUrl()
	{
		$value = parent::getAttribute('cacheUrl');

		$cacheUrl = ($value) ? craft()->config->parseEnvironmentString($value) : UrlHelper::getResourceUrl('minimee');

		return $this->forceTrailingSlash($cacheUrl);
	}

	/**
	 * @return Bool
	 */
	public function isResourceCache()
	{
		$cachePath = parent::getAttribute('cachePath');
		$cacheUrl = parent::getAttribute('cacheUrl');

		$cachePathEmpty = ! $cachePath;
		$cacheUrlEmpty = ! $cacheUrl;

		return ($cachePathEmpty && $cacheUrlEmpty);
	}
	
	/**
	 * @return String
	 */
	public function getBaseUrl()
	{
		return $this->forceTrailingSlash(craft()->getSiteUrl());
	}

	/**
	 * Inject our model attribute accessors.
	 *
	 * @param String $string
	 * @return String
	 */
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
	 * @return Bool
	 */
	public function exists()
	{
		return IOHelper::folderExists($this->cachePath);
	}
}
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
class Minimee_BaseAssetModel extends BaseModel implements Minimee_IAssetModel
{
	const TimestampZero = '0000000000';
	/*
	 * These are internal attributes only, not defined by Minimee_BaseAssetModel::defineAttributes()
	 * They are read-only, accessiable via magic getters e.g. $asset->contents or $asset->getContents()
	 *
	 * Leave as 'protected' so our parent classes can access them
	 */
	protected $_contents;
	protected $_lastTimeModified;
	protected $_exists;

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->filename;
	}

	/**
	 * @return Array
	 */
	public function defineAttributes()
	{
		return array(
			'filename'      => AttributeType::String,
			'filenamePath'  => AttributeType::String,
			'filenameUrl'   => AttributeType::String
		);
	}

	/**
	 * @return String
	 */
	public function getContents()
	{
		if($this->_contents === null)
		{
			$this->_contents = '';
		}

		return $this->_contents;
	}

	/**
	 * @return DateTime
	 */
	public function getLastTimeModified()
	{
		if($this->_lastTimeModified === null)
		{
			$this->_lastTimeModified = DateTime::createFromString(self::TimestampZero);
		}

		return $this->_lastTimeModified;
	}

	/**
	 * @return Bool
	 */
	public function exists()
	{
		if($this->_exists === null)
		{
			$this->_exists = false;
		}

		return $this->_exists;
	}

	/**
	 * Modified remove_double_slashes()
	 *
	 * If the string passed is a URL, it will preserve leading double slashes
	 *
	 * @param 	string	String to remove double slashes from
	 * @param 	boolean	True if string is a URL
	 * @return 	string	String without double slashes
	 */
	protected function removeDoubleSlashes($string, $url = FALSE)
	{
		// is our string a URL?
		if ($url)
		{
			// regex pattern removes all double slashes, preserving http:// and '//' at start
			return preg_replace("#([^:])//+#", "\\1/", $string);
		}
		
		// nope just a path
		else
		{
			// regex pattern removes all double slashes - straight from EE->functions->remove_double_slashes();
			return preg_replace("#(^|[^:])//+#", "\\1/", $string);
		}
	}
}
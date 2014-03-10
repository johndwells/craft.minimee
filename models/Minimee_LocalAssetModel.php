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
class Minimee_LocalAssetModel extends Minimee_AssetBaseModel
{
	/**
	 * Set our location based on contents of filename
	 *
	 *@ return String
	 */
	public function getContents()
	{
		if( ! $this->_contents)
		{
			$this->_contents = IOHelper::getFileContents($this->filenamePath);

			if($this->_contents === false)
			{
				throw new Exception('Could not get local asset: ' . $this->filenamePath);
			}
		}

		return $this->_contents;
	}

	/**
	 * Calculate the modified time of asset
	 *
	 * @return DateTime
	 */
	public function getLastTimeModified()
	{
		if( ! $this->_lastTimeModified)
		{
			$this->_lastTimeModified = IOHelper::getLastTimeModified($this->filenamePath);

			if($this->_lastTimeModified === false)
			{
				throw new Exception('Could not determine modification time of local asset: ' . $this->filenamePath);
			}
		}

		return $this->_lastTimeModified;
	}

	/**
	 * @return Bool
	 */
	public function exists()
	{
		return IOHelper::fileExists($this->filenamePath);
	}

	/**
	 * Attribute mutators
	 *
	 * @param String $name
	 * @param Mixed $value
	 * @param Void
	 */
	public function setAttribute($name, $value)
	{
		switch ($name) :
			case ('filenamePath') :
				$value = craft()->minimee_helper->removeDoubleSlashes($value, false);
			break;

			case ('filenameUrl') :
				$value = craft()->minimee_helper->removeDoubleSlashes($value, true);
			break;
		endswitch;

		parent::setAttribute($name, $value);
	}
}
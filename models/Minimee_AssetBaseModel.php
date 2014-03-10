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
class Minimee_AssetBaseModel extends BaseModel
{
	/*
	 * These are internal attributes only, not defined by Minimee_AssetBaseModel::defineAttributes()
	 * They are read-only, accessiable via magic getters e.g. $asset->contents
	 *
	 * Leave as 'protected' so our parent classes can access them
	 */
	protected $_contents;
	protected $_lastTimeModified;

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
			'filenameUrl'   => AttributeType::String,
			'type'          => array(AttributeType::Enum, 'values' => "css,js")
		);
	}
}
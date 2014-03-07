<?php
namespace Craft;

/**
 * Minimee by John D Wells
 *
 * @package   Minimee
 * @author    John D Wells
 * @copyright Copyright (c) 2012, John D Wells
 * @link      http://johndwells.com
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
     *
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
            'filename' => AttributeType::String
        );
    }
}
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

class Minimee_LocalAssetModel extends Minimee_AssetBaseModel
{
    /**
     * Set our location based on contents of filename
     *
     *@ return String the contents of the asset
     */
    public function getContents()
    {
        if( ! $this->_contents)
        {
        	$this->_contents = IOHelper::getFileContents($this->filename);

            if($this->_contents === false)
            {
                throw new Exception('Could not get local asset: ' . $this->filename);
            }
        }

    	return $this->_contents;
    }

    /**
     * Calculate the modified time of asset
     *
     * @return DateTime file's modification date
     */
    public function getLastTimeModified()
    {
        if( ! $this->_lastTimeModified)
        {
            $this->_lastTimeModified = IOHelper::getLastTimeModified($this->filename);

            if($this->_lastTimeModified === false)
            {
                throw new Exception('Could not determine modification time of local asset: ' . $this->filename);
            }
        }

        return $this->_lastTimeModified;
    }

    /**
     * Determine if asset exists
     *
     * @return Bool whether file exists or not
     */
    public function exists()
    {
        return IOHelper::fileExists($this->filename);
    }
}
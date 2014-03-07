<?php

namespace Craft;

class Minimee_CacheModel extends BaseModel
{
    protected $_filename          	= '';       // lastmodified value for cache
    protected $_filenameHash      	= '';       // a hash of all asset filenames together
    protected $_timestamp   		= '';       // eventual filename of cache

	/**
	 * @return Array
	 */
    public function defineAttributes()
    {
        return array(
            'assets'		=> AttributeType::Mixed,
            'type'			=> AttributeType::String,
            'cachePath'		=> AttributeType::String,
            'cacheUrl'		=> AttributeType::String
        );
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->filename;
    }

    public function getLastTimeModified()
    {
    	return ($this->_timestamp == 0) ? '0000000000' : $this->_timestamp;
    }

    public function getFilename()
    {
    	if( ! $this->_filename)
    	{
    		$this->_filename = $this->filenameHash . '.' . $this->lastTimeModified . '.' . $this->type;
    	}

    	return $this->_filename;
    }

    public function getFilenameHash()
    {
    	return sha1($this->_filenameHash);
    }

    public function setLastTimeModified(DateTime $lastTimeModified)
    {
    	$timestamp = $lastTimeModified->getTimestamp();
    	$this->_timestamp = max($this->lastTimeModified, $timestamp);
    }

    public function setFilenameHash($name)
    {
		// remove any cache-busting strings so the cache name doesn't change with every edit.
		// format: .v.1330213450
		$this->_filenameHash .= preg_replace('/\.v\.(\d+)/i', '', $name);
    }

    public function create()
    {
		// the eventual contents of our cache
		$contents = '';
		
		foreach($this->assets as $asset)
		{
			$contents .= craft()->minimee->minify($asset);
		}

		IOHelper::writeToFile($this->cachePath . $this->filename, $contents);

		$this->cleanup();

		return $this->cacheUrl . $this->filename;
    }

    public function cleanup()
    {
    	// only run cleanup if in devmode...?
    	if( ! craft()->config->get('devMode')) return;

    	$files = IOHelper::getFiles($this->cachePath);

    	foreach($files as $file)
    	{
    		$filenamePath = $this->cachePath . $this->filename;

			if ($file == '.' || $file == '..' || $file === $filenamePath) continue;

			$filenameHashPath = $this->cachePath . $this->filenameHash;

			if (strpos($file, $filenameHashPath) === 0)
			{
				// suppress errors by passing true as second parameter
				IOHelper::deleteFile($file, true);
			}
    	}
    }

    public function exists()
    {
        // loop through our files once
        foreach ($this->assets as $asset)
        {
            $this->lastTimeModified = $asset->lastTimeModified;
            $this->filenameHash = $asset->filename;
        }

        if( ! IOHelper::fileExists($this->cachePath . $this->filename))
        {
            return false;
        }

        return true;
    }
}
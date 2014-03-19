<?php namespace Craft;

use Guzzle\Http\Client;

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
class Minimee_RemoteAssetModel extends Minimee_AssetBaseModel
{
	/**
	 * Get the contents of the remote asset.
	 * 
	 * @return String
	 */
	public function getContents()
	{
		if( ! $this->_contents)
		{
			$client = new Client();
			$request = $client->get($this->filenameUrl);
			$response = $request->send();
			if ($response->isSuccessful())
			{
				$this->_contents = $response->getBody();
			}
			else
			{
				throw new Exception('Minimee could not get remote asset: ' . $this->filenameUrl);
			}
		}

		return $this->_contents;;
	}

	/**
	 * Return a very old DateTime, since it is too expensive to fetch a remote file's headers
	 * 
	 * @return DateTime
	 */
	public function getLastTimeModified()
	{
		return DateTime::createFromString('0000000000');
	}

	/**
	 * Always will return true, since it is too expensive to fetch a remote file
	 * 
	 * @return Bool
	 */
	public function exists()
	{
		return true;
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
				$value = $this->removeDoubleSlashes($value, true);
			break;

			case ('filenameUrl') :
				$value = $this->removeDoubleSlashes($value, true);
			break;
		endswitch;

		parent::setAttribute($name, $value);
	}
}
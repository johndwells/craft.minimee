<?php
namespace Craft;

use Guzzle\Http\Client;

/**
 * Minimee by John D Wells
 *
 * @package   Minimee
 * @author    John D Wells
 * @copyright Copyright (c) 2012, John D Wells
 * @link      http://johndwells.com
 */

class Minimee_RemoteAssetModel extends Minimee_AssetBaseModel
{
	/**
	 * Get the contents of the remote asset.
	 * 
	 * @return String the contents of the remote asset
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
				throw new Exception('Could not get remote asset: ' . $this->filenameUrl);
			}
		}

		return $this->_contents;;
	}

	/**
	 * Return a very old DateTime, since it is too expensive to fetch a remote file's headers
	 * 
	 * @return DateTime always will be far in the past
	 */
	public function getLastTimeModified()
	{
		return DateTime::createFromString('0000000000');
	}

	/**
	 * Always will return true, since it is too expensive to fetch a remote file
	 * 
	 * @return Bool will always be true
	 */
	public function exists()
	{
		return true;
	}
}
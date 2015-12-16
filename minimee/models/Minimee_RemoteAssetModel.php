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
class Minimee_RemoteAssetModel extends Minimee_BaseAssetModel
{
	const TimestampZero = '0000000000';

	protected $_client;

	public function __construct($attributes = array(), ClientInterface $client = null)
	{
		parent::__construct($attributes);

		if($client)
		{
			$this->_client = $client;
		}
	}

	/**
	 * Get the contents of the remote asset.
	 *
	 * @return String
	 */
	public function getContents()
	{
		if($this->_contents === null)
		{
			$response = $this->_sendClientRequest();

			if ($response->isSuccessful())
			{
				$this->_contents = $response->getBody(true);
			} else
			{
				throw new Exception('Minimee could not get remote asset: ' . $this->filenameUrl);
			}
		}

		return $this->_contents;;
	}

	/**
	 * Returns the filename URL with a valid protocol
	 *
	 * cURL can't (yet?) handle protocol relative URLs, so we match the session
	 * and use a secure or unsecure protocol to fetch the remote URLs contents.
	 *
	 * @return String
	 */
	public function getFilenameUrlWithProtocol()
	{
		if(stripos($this->filenameUrl, 'http') !== 0)
		{
			$protocol = craft()->request->isSecureConnection() ? 'https:' : 'http:';
			return $protocol . $this->filenameUrl;
		} else
		{
			return $this->filenameUrl;
		}
	}

	/**
	 * Return a very old DateTime, since it is too expensive to fetch a remote file's headers
	 *
	 * @return DateTime
	 */
	public function getLastTimeModified()
	{
		return DateTime::createFromString(self::TimestampZero);
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

	/**
	 * Either create a fresh instance of Guzzle\Http\Client, or pass the instance we were given during instantiation.
	 */
	protected function _getInstanceOfClient()
	{
		return minimee()->makeClient();
	}

	/**
	 * Prepare & return response from sending a client request
	 */
	protected function _sendClientRequest()
	{
		$client = $this->_getInstanceOfClient();

		$request = $client->get($this->getFilenameUrlWithProtocol());

		return $request->send();
	}
}

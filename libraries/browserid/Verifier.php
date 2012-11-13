<?php
/**
 * PHP5-BrowserID
 *
 * Copyright (c) 2011 Ramon Torres
 *
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) 2011 Ramon Torres
 * @license The MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace browserid;

/**
 * Client for BrowserID's assertion verification service
 *
 * @package browserid
 */
class Verifier {

	/**
	 * The BrowserID's assertion verification service endpoint
	 */
	const ENDPOINT = 'https://verifier.login.persona.org/verify';

	/**
	 * The hostname and optional port of your site
	 *
	 * @var string
	 */
	public $audience;

	public function __construct($audience) {
		$this->audience = $audience;
	}

	/**
	 * Verifies a BrowserID assertion
	 *
	 * @param string $assertion  assertion to be verified
	 * @return object
	 *           BrowserID verification response object with the following attributes:
	 *             email: email address of the user
	 *             audience: hostname that the assertion is valid for
	 *             expires: expiration timestamp of the assertion
	 *             issuer: the entity who issued the assertion
	 * @throws \browserid\Exception
	 */
	public function verify($assertion) {
		$response = $this->_post(array(
			'audience' => $this->audience,
			'assertion' => $assertion
		));

		if ($response->status !== 'okay') {
			throw new Exception('Invalid assertion - ' . $response->reason);
		}

		return $response;
	}

	/**
	 * Makes an HTTP POST request to verification endpoint
	 *
	 * @param array $data  data to be sent to endpoint
	 * @return object  verification response
	 * @throws \browserid\Exception
	 */
	protected function _post($data) {
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL            => static::ENDPOINT,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => json_encode($data),
			CURLOPT_HEADER         => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HTTPHEADER => array('Content-Type: application/json')
		));

		$response = curl_exec($ch);
		curl_close($ch);

		if ($response === false) {
			throw new Exception('Failed to contact BrowserID verification service');
		}

		$decodedResponse = json_decode($response);
		if (!$decodedResponse) {
			throw new Exception('Response is not valid JSON');
		}

		return $decodedResponse;
	}
}

<?php

class API {

	// Create the page.
	public function __construct($url, $debug = false) { 
		if (substr($url, -1) != '/') { $url .= '/'; }
		$this->url = $url;
		$this->debug = $debug;
	}

	
	public function checkYubikey($token) {
		$submitURL = $this->url.'checkAuthentication';
		$args = array(
			'type' => 'yubikey',
			'token' => $token
		);

		return $this->sendRequest($submitURL, $args);
	}
	
	public function checkCredentials($username, $password) {
		$submitURL = $this->url.'checkAuthentication';
		$args = array(
			'type' => 'credentials',
			'username' => $username, 
			'passwordHash' => sha1($username.$password)
		);

		return $this->sendRequest($submitURL, $args);
	}

	private function sendRequest($url, $args) {
		$curlRes = curl_init();
		curl_setopt($curlRes, CURLOPT_URL, $url);

		$postString = '';
		foreach ($args as $key => $value) {
			$postString .= $key.'='.urlencode($value).'&';
		}
		$postString = substr($postString, 0, -1);

		curl_setopt($curlRes, CURLOPT_HEADER, false);		
		curl_setopt($curlRes, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlRes, CURLOPT_POSTFIELDS, $postString);

		$result = curl_exec($curlRes);

		curl_close ($curlRes);
		
		if ($this->debug) {
			return array('type' => 'info',
					'tagline' => 'API Debugging...',
					'info' => '<pre>'.htmlspecialchars($result).'</pre>');
		} else {
			$result = json_decode($result, true);

			if ($result['message']) {
				$return['type'] = $result['message'];
				foreach ($result as $key => $value) {
					$return[$key] = $value;
				}
				return $return;
			} elseif ($result == null) {
				 return array('type' => 'error',
					'tagline' => 'API Error',
					'error' => 'API returned no usable output');
			} else {
				return true;
			}
		}
	}
}
?>
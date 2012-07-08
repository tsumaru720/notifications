<?php

class API {

	// Create the page.
	public function __construct($url) { 
		if (substr($url, -1) != '/') { $url .= '/'; }
		$this->url = $url;
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
			'password' => sha1($username.$password)
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
		
		$result = json_decode($result, true);
		
		if ($result['error']) {
			return array('type' => 'error', 'error' => $result['error']);
		} elseif ($result['info']) {
			return array('type' => 'info', 'info' => $result['info']);
		} else {
			return true;
		}
	}
}
?>
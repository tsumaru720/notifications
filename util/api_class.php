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

		$this->sendRequest($submitURL, $args);

		return true;
	}
	
	public function checkCredentials($username, $password) {
		return true;
	}

	private function sendRequest($url, $args) {
		$curlRes = curl_init();
		curl_setopt($curlRes, CURLOPT_URL, $url);

		$postString = '';
		foreach ($args as $key => $value) {
			$postString .= $key.'='.urlencode($value).'&';
		}
		$postString = substr($postString, 0, -1);
		
		curl_setopt($curlRes, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlRes, CURLOPT_POSTFIELDS, $postString);

		$result = curl_exec($curlRes);
		curl_close ($curlRes);

		//var_dump($result);

		return true;
	}
}
?>
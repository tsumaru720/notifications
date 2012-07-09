<?php

class API {

	// Create the page.
	public function __construct($url, $debug = false) { 
		if (substr($url, -1) != '/') { $url .= '/'; }
		$this->url = $url;
		$this->debug = $debug;
	}

	
	public function checkYubikey($token) {
		$submitURL = $this->url.'checkAuthentication/yubikey';
		$args = array(
			'token' => $token,
			'computer_id' => $_COOKIE['computer_id']
		);

		return $this->sendRequest($submitURL, $args);
	}
	
	public function checkCredentials($username, $password) {
		$submitURL = $this->url.'checkAuthentication/credentials';
		$args = array(
			'username' => $username, 
			'passwordHash' => sha1($username.$password),
			'computer_id' => $_COOKIE['computer_id']
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

		if (!empty($_SESSION['api_session'])) { $postString .= '&session='.$_SESSION['api_session']; }

		/*
		var_dump($postString);
		var_dump($_SESSION);
		die();*/


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

			if ($result['session']) {
				$_SESSION['api_session'] = $result['session'];
				//Remove session id from results as we only need it here
				unset($result['session']);
			}
			if ($result['error']) {
				$return['type'] = 'error';
				foreach ($result as $key => $value) {
					$return[$key] = $value;
				}
				return $return;
			} elseif ($result == null) {
				 return array('type' => 'error',
					'tagline' => 'API Error',
					'error' => 'API_ERROR');
			} else {
				return true;
			}
		}
	}
}
?>
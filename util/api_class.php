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
			'token' => $token
		);

		return $this->sendRequest($submitURL, $args);
	}
	
	public function checkCredentials($username, $password) {
		$submitURL = $this->url.'checkAuthentication/credentials';

		$username = strtolower($username);

		$args = array(
			'username' => $username, 
			'passwordHash' => sha1($username.$password)
		);

		return $this->sendRequest($submitURL, $args);
	}

	public function validateComputer($code) {
		$submitURL = $this->url.'checkAuthentication/validate';
		$args = array(
			'code' => $code
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

//2e347d84a55997137286361e5964485deddf6545
//$_COOKIE['device_id'] = '';

		//Append some variables to the end of the API request.
		if (!empty($_SESSION['api_session'])) { $postString .= '&session='.$_SESSION['api_session']; }
		$postString .= '&device_id='.urlencode($_COOKIE['device_id']);
		$postString .= '&client_ip='.$_SERVER['REMOTE_ADDR'];

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
			if ($result['info']) {
				$return['type'] = 'info';
				foreach ($result as $key => $value) {
					$return[$key] = $value;
				}
				return $return;
			} elseif ($result == null) {
				 return array('type' => 'info',
					'tagline' => 'API Error',
					'info' => 'API_ERROR');
			} else {
				return $result;
			}
		}
	}
}
?>
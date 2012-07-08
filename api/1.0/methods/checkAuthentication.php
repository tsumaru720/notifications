<?php
//apiOut(array('info' => '<pre>'.print_r($_POST, true).'</pre>', 'tagline' => 'API says...'));

if ($_POST['type'] == 'yubikey') {
	return authenticateYubikey($_POST['token']);
} elseif ($_POST['type'] == 'credentials') {
	return authenticateCredentials($_POST['username'], $_POST['passwordHash']);
} else {
	return apiOut(array('message' => 'error',
			'error' => 'No data provided to API'));
}


function authenticateYubikey($token) {
	global $CONFIG;
	if (preg_match('/^[cbdefghijklnrtuv]{44}$/i', $token)) {
		//Do actual check here
		require_once 'Auth/Yubico.php';

		$yubi = new Auth_Yubico($CONFIG['YUBICO_ID'], $CONFIG['YUBICO_KEY']);
		$auth = $yubi->verify($token);
		if (PEAR::isError($auth)) {
			return apiOut(array('message' => 'error',
						'error' => 'Failed to authenticate token: '.$auth->getMessage(),
						'field' => 'token'));
		}
		return apiOut(array('message' => 'success',
					'success' => 'Appears OK - Token is valid - User ID: '.substr($token,0,12)));
		//return true;
	} else {
		return apiOut(array('message' => 'error',
					'error' => 'Yubikey token is not valid',
					'field' => 'token'));
	}
}

function authenticateCredentials($username, $passwordHash) {
	if (empty($username)) {
		return apiOut(array('message' => 'error',
					'error' => 'Username cannot be empty',
					'field' => 'username'));
	} elseif (!preg_match('/^[0-9a-f]{40}$/i', $passwordHash)) {
		return apiOut(array('message' => 'error',
					'error' => 'Password hash provided does not appear to be valid SHA1',
					'field' => 'passwordHash'));
	} elseif (sha1($username) == $passwordHash) {
		return apiOut(array('message' => 'error',
					'error' => 'Password cannot be empty',
					'field' => 'passwordHash'));
	} else {
		//Do actual check here
		return apiOut(array('message' => 'success',
					'success' => 'Appears OK - Check credentials'));
		//return true;
	}
}


?>
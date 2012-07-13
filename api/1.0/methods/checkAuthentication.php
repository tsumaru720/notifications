<?php
//apiOut(array('info' => '<pre>'.print_r($_POST, true).'</pre>', 'tagline' => 'API says...'));

if ($requestDetails['action'] == 'yubikey') {
	return authenticateYubikey($_POST['token'], $_POST['computer_id']);
} elseif ($requestDetails['action'] == 'credentials') {
	return authenticateCredentials($_POST['username'], $_POST['passwordHash'], $_POST['computer_id']);
} elseif ($requestDetails['action'] == 'validate') {
	return validateComputer($_POST['code']);
} else {
	return apiOut(array('info' => 'INVALID_METHOD_REQUEST'));
}


function validateComputer($code) {
	global $mysql;

	$data = array(':code' => $code);
	$query = $mysql->query("SELECT validated, validation_code FROM device_authorizations LEFT JOIN devices ON device_authorizations.device_id = devices.id WHERE validation_code = :code;", $data);
	$device = $mysql->fetch($query);

	if ($device) {
		if ($device['validated'] == '0') {
			$data = array(':time' => time(), ':validation_code' => $device['validation_code']);
			$query = $mysql->query("UPDATE device_authorizations SET  `validated` =  1, `last_seen` =  :time WHERE validation_code = :validation_code;", $data);
			return apiOut(array('status' => 'SUCCESS'));
		} else {
			return apiOut(array('info' => 'ALREADY_VALIDATED'));
		}
	} else {
		return apiOut(array('info' => 'INVALID_CODE'));
	}
}

function authenticateYubikey($token, $computerID) {
	global $CONFIG, $mysql;

	if (preg_match('/^[cbdefghijklnrtuv]{44}$/i', $token)) {
		//Appears to be a valid token - Authenticate it
		require_once 'Auth/Yubico.php';
		$yubi = new Auth_Yubico($CONFIG['YUBICO_ID'], $CONFIG['YUBICO_KEY']);
		$auth = $yubi->verify($token);

		if (PEAR::isError($auth)) {
			//Something is wrong with the token provided
			return apiOut(array('info' => $auth->getMessage()));
		} else {
			//Key has been authenticated!
			$yubikey_id = substr($token,0,12); //Yubikey public ID is first 12 chars...
			$data = array(':keyid' => $yubikey_id);

			$query = $mysql->query("SELECT user_id, email_address, real_name, public_id, requires_authorization_yubikey FROM yubikeys LEFT JOIN users ON yubikeys.user_id = users.id WHERE public_id = :keyid;", $data);
			$info = $mysql->fetch($query);
			if (!$info) {
				//This key is not known to us...
				return apiOut(array('info' => 'UNKNOWN_KEY',
						'yubikey_id' => $key_id));
			} else {
				//Successful Authentication
				if ($info['requires_authorization_yubikey'] == '1') {
					//Need to check if computer is authorized for this account
					$device = checkDevice($info, $yubikey_id);
				}
				//Logged in Successfully - Start a session id
				session_start();
				$_SESSION['authenticated'] = true;
				$_SESSION['user_id'] = $info['user_id'];
				$_SESSION['ipAddr'] = $_POST['client_ip'];
				if ($device) {
					$_SESSION['device_id'] = $device['device_id'];
					$_SESSION['auth_id'] = $device['auth_id'];
					update_activity($_SESSION['ipAddr'], $_SESSION['user_id'], $_SESSION['device_id'], $_SESSION['auth_id']);
				} else {
					update_activity($_SESSION['ipAddr'], $_SESSION['user_id']);
				}
				$_SESSION['auth_type'] = 'yubikey '.$yubikey_id;
				return apiOut(array('status' => 'SUCCESS',
							'session' => session_id()));
			}
		}
	} else {
		return apiOut(array('info' => 'INVALID_TOKEN'));
	}
}

function authenticateCredentials($username, $passwordHash, $computerID) {
	global $mysql;

	if (empty($username)) {
		return apiOut(array('info' => 'EMPTY_USERNAME'));
	} elseif (!preg_match('/^[0-9a-f]{40}$/i', $passwordHash)) {
		return apiOut(array('info' => 'INVALID_HASH'));
	} elseif (sha1($username) == $passwordHash) {
		return apiOut(array('info' => 'EMPTY_PASSWORD'));
	} else {
		$username = strtolower($username);
		$data = array(':username' => $username);
		$query = $mysql->query("SELECT id AS user_id, email_address, real_name, requires_authorization_credentials, password_hash as stored_hash FROM users WHERE username = :username", $data);
		$info = $mysql->fetch($query);

		$newHash = crypt($passwordHash, $info['stored_hash']);

		if ($newHash == $info['stored_hash']) {
			//Successful Authentication
			if ($info['requires_authorization_credentials'] == '1') {
				//Need to check if computer is authorized for this account
				$device = checkDevice($info);
				var_dump($device);
			}
			//Logged in Successfully - Start a session id
			session_start();
			$_SESSION['authenticated'] = true;
			$_SESSION['user_id'] = $info['user_id'];
			$_SESSION['ipAddr'] = $_POST['client_ip'];
			if ($device) {
				$_SESSION['device_id'] = $device['device_id'];
				$_SESSION['auth_id'] = $device['auth_id'];
				update_activity($_SESSION['ipAddr'], $_SESSION['user_id'], $_SESSION['device_id'], $_SESSION['auth_id']);
			} else {
				update_activity($_SESSION['ipAddr'], $_SESSION['user_id']);
			}
			$_SESSION['auth_type'] = 'credentials';
			return apiOut(array('status' => 'SUCCESS',
						'session' => session_id()));
		} else {
			return apiOut(array('info' => 'INVALID_CREDENTIALS'));
		}
	}
}

function checkDevice($info, $yubikey_id = null) {
	global $mysql, $CONFIG;
	if ($_POST['device_id'] == "") { //No computer id provided - generate new one

		$new_device = newDevice($info, $yubikey_id);

		return apiOut(array('info' => 'DEVICE_ID_GENERATED',
					'device_id' => $new_device['public']));

	} else { //Computer id provided, need to check its in the DB.
		$device_hash = sha1($CONFIG['DEVICE_HASH_SALT'].$_POST['device_id']); 
		$data = array(':device_hash' => $device_hash, ':uid' => $info['user_id']);
		$query = $mysql->query("SELECT device_id, device_authorizations.id AS auth_id, validated, public_hash FROM device_authorizations LEFT JOIN devices ON device_authorizations.device_id = devices.id LEFT JOIN users ON users.id = device_authorizations.user_id WHERE users.id = :uid AND device_hash = :device_hash;", $data);
		$device = $mysql->fetch($query);
		if (!$device) {
			//return apiOut(array('info' => 'BAD_DEVICE_HASH'));
			$new_device = newDevice($info, $yubikey_id);
			return apiOut(array('info' => 'DEVICE_ID_GENERATED',
					'device_id' => $new_device['public']));
		}
		if ($device['validated'] == '0') {
			return apiOut(array('info' => 'VALIDATION_ALREADY_SENT',
					'device_id' => $device['public_hash']));
		}
	}
	return $device;
}

function newDevice($info, $yubikey_id = null) {
		$new_device = generateDeviceHash();
		$new_device['id'] = storeDeviceHash($new_device['private'], $new_device['public'], $_POST['client_ip']);
		associateHashToUser($new_device['id'], $info['user_id'], $new_device['validation_code'], $_POST['client_ip'], $yubikey_id);
		sendValidationEmail($info['email_address'], $info['real_name'], $new_device['validation_code']);

		return $new_device;
}

function sendValidationEmail ($email, $name, $validation_code) {
	$to      = $email;
	$subject = 'Computer Authorization';
	$message = "Hello ".$name.", 

	It appears that you have tried to log in to your account from a computer that has not yet been authorized.
	If this is correct, please click the link below.
	
	http://dev.agari.co/notifications/validate?code=".$validation_code."
	
	If you are not aware of this authorization attempt, we suggest you log in to your account and check your Access settings to view more details about this attempt.
	
	Best Regards, 
	Site";

	mail($to, $subject, $message, 'From: Site <website@agari.co>');
}

function associateHashToUser($device_id, $user_id, $validation_code, $ipAddr, $yubikey_id = null) {
	global $mysql;

	if ($yubikey_id) {
		$logon_type = 'YubiKey: '. $yubikey_id;
	} else {
		$logon_type = 'Credentials';
	}

	$data = array(':device_id' => $device_id,
			':user_id' => $user_id,
			':created' => time(),
			':validation_code' => $validation_code,
			':last_seen' => time(),
			':logon_type' => $logon_type,
			':ip' => $ipAddr);

	$query = $mysql->query("INSERT INTO  device_authorizations (
				`id` ,
				`device_id` ,
				`user_id` ,
				`validated`, 
				`created` ,
				`validation_code`,
				`last_seen`,
				`last_logon_type` ,
				`last_logon_ip`
				)
				VALUES (
				NULL ,  :device_id, :user_id, 0, :created, :validation_code, :last_seen, :logon_type, :ip)", $data);
	return true;
}

function storeDeviceHash($private_device_hash, $public_device_hash, $ipAddr) {
	global $mysql;
	$data = array(':private_hash' => $private_device_hash,
			':public_hash' => $public_device_hash,
			':date_added' => time(),
			':last_seen' => time(),
			':ip' => $ipAddr);

	$query = $mysql->query("INSERT INTO  devices (
				`id` ,
				`device_hash` ,
				`public_hash`, 
				`date_added` ,
				`last_seen`, 
				`last_ip`
				)
				VALUES (
				NULL ,  :private_hash, :public_hash, :date_added, :last_seen, :ip)", $data);
	return $mysql->getInsertID();
}

function generateDeviceHash() {
	global $mysql, $CONFIG;

	$public_device_hash = generateRandomHash();
	$private_device_hash = sha1($CONFIG['DEVICE_HASH_SALT'].$public_device_hash);

	$validation_code = generateRandomHash();

	return array('public' => $public_device_hash, 
			'private' => $private_device_hash,
			'validation_code' => $validation_code);
}

function generateRandomHash() {
	return sha1(
		base64_encode(
			bin2hex(
				openssl_random_pseudo_bytes(20)
			)
		)
	);
}

?>
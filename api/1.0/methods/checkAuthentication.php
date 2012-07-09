<?php
//apiOut(array('info' => '<pre>'.print_r($_POST, true).'</pre>', 'tagline' => 'API says...'));

if ($requestDetails['action'] == 'yubikey') {
	return authenticateYubikey($_POST['token'], $_POST['computer_id']);
} elseif ($requestDetails['action'] == 'credentials') {
	return authenticateCredentials($_POST['username'], $_POST['passwordHash'], $_POST['computer_id']);
} else {
	return apiOut(array('error' => 'INVALID_METHOD_REQUEST'));
}


function authenticateYubikey($token, $computerID) {
	global $CONFIG, $mysql;

	if (preg_match('/^[cbdefghijklnrtuv]{44}$/i', $token)) {
		//Do actual check here

		if ($_SESSION['computer_id']) {
			if ($computerID != $_SESSION['computer_id']) {
				//We've been passed a different id to what we think we should have. Use ours instead
				$computerID = $_SESSION['computer_id'];
			}
		}

		require_once 'Auth/Yubico.php';
		$yubi = new Auth_Yubico($CONFIG['YUBICO_ID'], $CONFIG['YUBICO_KEY']);
		$auth = $yubi->verify($token);

		if (PEAR::isError($auth)) {
			return apiOut(array('error' => $auth->getMessage(),
						'field' => 'token'));
		}

		$key_id = substr($token,0,12);
		$data = array(':keyid' => $key_id);
		$query = $mysql->query("SELECT user_id, password_hash AS stored_hash, email_address, real_name, public_id FROM yubikeys LEFT JOIN users ON yubikeys.user_id = users.id WHERE public_id = :keyid;", $data);
		$info = $mysql->fetch($query);
		if (!$info) {
			return apiOut(array('error' => 'VALID_UNKNOWN_KEY',
					'key_id' => $key_id));
		}

		$data = array(':compid' => $computerID);
		$query = $mysql->query("SELECT id_string FROM computers LEFT JOIN users ON computers.user_id = users.id WHERE id_string = :compid AND validated = 1;", $data);
		$comp = $mysql->fetch($query);

		if (!$comp) {
			//Computer doesnt appear authorized or not validated
			$data = array(':compid' => $computerID, 'uid' => $info['user_id']);
			$query = $mysql->query("SELECT id_string FROM computers WHERE id_string = :compid AND user_id = :uid;", $data);
			$id_check = $mysql->fetch($query);

			if ($id_check == false) {
				//Doesnt exist in db, so make new id
				generateComputerValidationCode($info, $key_id);
			} else {
				//Already in DB - not been validated
				return apiOut(array('error' => 'VALIDATION_ALREADY_SENT',
					'id' => $computerID,
					'session' => session_id()));
			}
		} else {
			//Computer is Authorized
			return apiOut(array('error' => 'SUCCESS'));
		}
		//return true;
	} else {
		return apiOut(array('error' => 'INVALID_TOKEN',
					'field' => 'token'));
	}
}

function authenticateCredentials($username, $passwordHash, $computerID) {
	global $mysql;

	if (empty($username)) {
		return apiOut(array('error' => 'EMPTY_USERNAME',
					'field' => 'username'));
	} elseif (!preg_match('/^[0-9a-f]{40}$/i', $passwordHash)) {
		return apiOut(array('error' => 'INVALID_HASH',
					'field' => 'passwordHash'));
	} elseif (sha1($username) == $passwordHash) {
		return apiOut(array('error' => 'EMPTY_PASSWORD',
					'field' => 'passwordHash'));
	} else {
		//Do actual check here

		if ($_SESSION['computer_id']) {
			if ($computerID != $_SESSION['computer_id']) {
				//We've been passed a different id to what we think we should have. Use ours instead
				$computerID = $_SESSION['computer_id'];
			}
		}

		$username = strtolower($username);
		$data = array(':username' => $username);
		$query = $mysql->query("SELECT id AS user_id, password_hash as stored_hash, email_address, real_name FROM users WHERE username = :username", $data);
		$info = $mysql->fetch($query);

		$newHash = crypt($passwordHash, $info['stored_hash']);
		if ($newHash == $info['stored_hash']) {
			//Successful Authentication

			$data = array(':compid' => $computerID);
			$query = $mysql->query("SELECT id_string FROM computers LEFT JOIN users ON computers.user_id = users.id WHERE id_string = :compid AND validated = 1;", $data);
			$comp = $mysql->fetch($query);

			if (!$comp) {
				//Computer doesnt appear authorized or not validated

				$data = array(':compid' => $computerID, 'uid' => $info['user_id']);
				$query = $mysql->query("SELECT id_string FROM computers WHERE id_string = :compid AND user_id = :uid;", $data);
				$id_check = $mysql->fetch($query);

				if ($id_check == false) {
					//Doesnt exist in db, so make new id
					generateComputerValidationCode($info);
				} else {
					//Already in DB - not been validated
					return apiOut(array('error' => 'VALIDATION_ALREADY_SENT',
						'id' => $computerID,
						'session' => session_id()));
				}
			} else {
				//Computer is Authorized
				return apiOut(array('error' => 'SUCCESS'));
			}

		} else {
			return apiOut(array('error' => 'INVALID_CREDENTIALS'));
		}
	}
}

function generateComputerValidationCode($account, $key_id = null) {
	global $mysql;
	$compID = generateRandomHash();
	$code = generateRandomHash();

	if ($key_id) {
		$logonString = 'YubiKey: '. $key_id;
	} else {
		$logonString = 'Username and Password';
	}

	$data = array(':compid' => $compID,
			':vcode' => $code,
			':uid' => $account['user_id'],
			':time' => time(),
			':logontype' => $logonString);

	$query = $mysql->query("INSERT INTO  computers (
				`id` ,
				`id_string` ,
				`validation_string` ,
				`validated`, 
				`user_id` ,
				`date_added` ,
				`last_seen` ,
				`last_logon_type`, 
				`last_connected_ip`
				)
				VALUES (
				NULL ,  :compid,  :vcode, 0, :uid,  :time,  0, :logontype, '')", $data);

	$_SESSION['authenticated'] = true;
	$_SESSION['authorized'] = false;
	$_SESSION['computer_id'] = $compID;

	$to      = $account['email_address'];
	$subject = 'Computer Authorization';
$message = "Hello ".$account['real_name'].", 

It appears that you have tried to log in to your account from a computer that has not yet been authorized.
If this is correct, please click the link below.

http://dev.agari.co/notifications/?validation_code=".$code."

If you are not aware of this authorization attempt, we suggest you log in to your account and check your Access settings to view more details about this attempt.

Best Regards, 
Site";

	mail($to, $subject, $message, 'From: Site <website@agari.co>');

	return apiOut(array('error' => 'VALIDATION_SENT',
				'id' => $compID,
				'session' => session_id()));
}

function generateRandomHash() {
	return sha1(
		mt_rand(1000000000,9999999999)
		.
		mt_rand(1000000000,9999999999)
		.
		mt_rand(1000000000,9999999999)
	);
}

?>
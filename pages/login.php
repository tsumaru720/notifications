<?php	

$isSubmit = !empty($_POST);
$yubikey = isset($_POST['yubikey']) ? $_REQUEST['yubikey'] : '';

$factory = new PageFactory();

if ($isSubmit) {

	$result = $api->checkYubikey($yubikey);

	if ($result === true) {
		//header('Location: /loggedin.php');
		die();
	} else {

		if ($result['error'] == 'VALIDATION_SENT' || $result['error'] == 'VALIDATION_ALREADY_SENT') {
			$result['type'] = 'info';
			$result['tagline'] = 'Validation email sent';
			$result['info'] = "You have successfully authenticated, however it appears this PC is not authorized to access your account.<br><br>A validation email has been sent to you with further instructions.";

			if (!empty($_COOKIE['computer_id'])) {
				setcookie('computer_id', '', time() - 3600);
			}
			setcookie('computer_id', $result['id'], time()+60*60*24*30);
		}


		// Display Errors
		$factory->setVar('messageType', $result['type']);
		$factory->setVar('messageTopic', (!empty($result['tagline']) ? $result['tagline'] : ''));
		$factory->setVar('messageText', $result[$result['type']]);

		$message = $factory->newPage('message.tpl');

		$factory->setVar('message', $message);
		//End


	}
}

$page = $factory->newPage('login.tpl');

if ($result['field'] == 'token') {
	$page->setVar('tokenError', true);
}

$page->display();

?>
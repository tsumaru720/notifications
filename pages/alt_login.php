<?php	

$isSubmit = !empty($_POST);
$username = isset($_POST['username']) ? $_REQUEST['username'] : '';
$password = isset($_POST['password']) ? $_REQUEST['password'] : '';

$factory = new PageFactory();

if ($isSubmit) {

	$result = $api->checkCredentials($username, $password);

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

$page = $factory->newPage('alt_login.tpl');

if ($result['field'] == 'username') {
	$page->setVar('usernameError', true);
} elseif ($result['field'] == 'passwordHash') {
	$page->setVar('passwordError', true);
}

$page->display();

?>
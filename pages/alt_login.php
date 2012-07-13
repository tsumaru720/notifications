<?php	

if ($_SESSION['authenticated']) {
	echo 'ALREADY AUTHENTICATED';
}

$isSubmit = !empty($_POST);
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$factory = new PageFactory();

if ($isSubmit) {

	$result = $api->checkCredentials($username, $password);

	if (!$result['info']) {
		$_SESSION['authenticated'] = true;
		echo 'OK!';
		die();
	} else {
		if ($result['info'] == 'DEVICE_ID_GENERATED' || $result['info'] == 'VALIDATION_ALREADY_SENT') {

			createMessage($factory, 'info', 'Validation email sent', 'You have successfully authenticated, however it appears this PC is not authorized to access your account.<br><br>A validation email has been sent to you with further instructions.');

			if (!empty($_COOKIE['device_id'])) {
				setcookie('device_id', '', time() - 3600);
			}
			setcookie('device_id', $result['device_id'], time()+60*60*24*30);
		} else {
			createMessage($factory, $result['type'], $result['tagline'], $result[$result['type']]);
		}
	}
}

$page = $factory->newPage('alt_login.tpl');

$page->display();


function createMessage($factory, $type, $tagline, $text) {
	$factory->setVar('messageType', $type);
	$factory->setVar('messageTopic', $tagline);
	$factory->setVar('messageText', $text);

	$message = $factory->newPage('message.tpl');

	$factory->setVar('message', $message);
}

?>
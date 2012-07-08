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
		$factory->setVar('messageType', $result['type']);
		$factory->setVar('messageTopic', (!empty($result['tagline']) ? $result['tagline'] : ''));
		$factory->setVar('messageText', $result[$result['type']]);

		$message = $factory->newPage('message.tpl');

		$factory->setVar('message', $message);


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
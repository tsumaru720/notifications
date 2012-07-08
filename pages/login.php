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
		$factory->setVar('messageType', $result['type']);
		//$factory->setVar('messageTopic', 'There was an error...');
		$factory->setVar('messageText', $result[$result['type']]);

		$message = $factory->newPage('message.tpl');

		$factory->setVar('message', $message);


	}
}

$page = $factory->newPage('login.tpl');

$page->display();

?>
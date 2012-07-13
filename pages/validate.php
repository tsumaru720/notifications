<?php	

$validation_code = $_GET['code'];

$factory = new PageFactory();

if (!empty($validation_code)) {
	$result = $api->validateComputer($validation_code);

	if (!$result['info']) {
		//Success
		createMessage($factory, 'success', 'Validated!', 'Congratulations, you have successfully validated this computer. You will need to log in again to access your account.');
	} else {
		//Error
		createMessage($factory, 'info', $result['tagline'], $result['info']);
	}
} else {
	createMessage($factory, 'error', 'Invalid code', 'You appear to have not provided a validation code. Please double check the link in your email. Copy it into your browser if necessary');
}



$page = $factory->newPage('validate.tpl');
$page->display();


function createMessage($factory, $type, $tagline, $text) {
	$factory->setVar('messageType', $type);
	$factory->setVar('messageTopic', $tagline);
	$factory->setVar('messageText', $text);

	$message = $factory->newPage('static-message.tpl');

	$factory->setVar('message', $message);
}

?>
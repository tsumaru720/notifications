<?php	

$isSubmit = !empty($_POST);
$yubikey = isset($_POST['yubikey']) ? $_REQUEST['yubikey'] : '';

$page = new Page('login.tpl');

if ($isSubmit) {

	$api->checkYubikey($yubikey);
	//$success = $api->checkLogin($login, $pass);

	if ($success) {
		//header('Location: /loggedin.php');
		die();
	} else {
		$page->setVar('error', 'Login failed.');
	}
}

$page->display();

?>
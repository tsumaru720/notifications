<?php	

$isSubmit = !empty($_POST);
$yubikey = isset($_POST['yubikey']) ? $_REQUEST['yubikey'] : '';

$page = new Page('login.tpl');

if ($isSubmit) {

	var_dump("Check Yubikey Login stuff via API");
	$success = true;
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
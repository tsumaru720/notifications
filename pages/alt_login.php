<?php	

$isSubmit = !empty($_POST);
$username = isset($_POST['username']) ? $_REQUEST['username'] : '';
$password = isset($_POST['password']) ? $_REQUEST['password'] : '';

$page = new Page('alt_login.tpl');

if ($isSubmit) {

	var_dump("Check Login stuff via API");
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
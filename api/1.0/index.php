<?php

if ($_POST['session']) { 
	session_id($_POST['session']);
	session_start();
}

function fixRequestURI() {
	$len = strlen(substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'], "/")));

	$tmp = substr($_SERVER['REQUEST_URI'],$len);

	return ($tmp == false ? "" : $tmp);
}

function parseRequestURI() {
	$pageLocation = 'methods';

	if ($_SERVER['REDIRECT_STATUS'] == '200' || $_SERVER['REDIRECT_STATUS'] == null) {
		$requestURI = substr($_SERVER['REQUEST_URI'],1);
		$queryString = preg_split('/\?/',$requestURI);
		$getParams = preg_split('/\//',$queryString[0]);
		$methodAction = '';

		//Nothing requested - use Index by default
		if (empty($getParams[0])) {
			return array('info' => 'INVALID_REQUEST_EMPTY_REQUEST');
		}

		$methodName = urldecode($getParams[0]);
		
		$page = $pageLocation.'/'.$methodName.'.php';

		if (array_key_exists(1,$getParams)) {
			if (!empty($getParams[1])) {
				$methodAction = urldecode($getParams[1]);
			}
		}
	} else {
		return array('info' => 'REDIRECT_STATUS_'.$_SERVER['REDIRECT_STATUS']);
	}
	return array(
		'method' => $page,
		'request' => $getParams[0],
		'action' => $getParams[1],
		);
}

$_SERVER['REQUEST_URI'] = fixRequestURI();
$requestDetails = parseRequestURI();

require_once('config.php');
require_once('util/apiOut.php');
require_once('util/mysql.php');
require_once('util/update_activity.php');

if ($requestDetails['error']) {
	apiOut($requestDetails);
}


if (!$_POST['client_ip']) {
	apiOut(array('info' => 'CONFIRM_CLIENT_IP'));
}

if ($_SESSION['authenticated']) {

	if ($_SESSION['ipAddr'] != $_POST['client_ip']) {
		apiOut(array('info' => 'DEAUTHENTICATED_SESSION_HIJACKED'));
	}

	if ($_SESSION['device_id']) {
		update_activity($_SESSION['ipAddr'], $_SESSION['user_id'], $_SESSION['device_id'], $_SESSION['auth_id']);
	} else {
		update_activity($_SESSION['ipAddr'], $_SESSION['user_id']);
	}
}

$mysql = new MySQL($CONFIG['SQL_HOSTNAME'], $CONFIG['SQL_PORT'], $CONFIG['SQL_USERNAME'], $CONFIG['SQL_PASSWORD'], $CONFIG['SQL_DATABASE']);

if (file_exists($requestDetails['method'])) {
        include($requestDetails['method']);
} else {
	apiOut(array('info' => 'INVALID_REQUEST_NO_SUCH_METHOD'));
}



?>
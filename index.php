<?php

session_start();

function fixRequestURI() {
	$len = strlen(substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'], "/")));

	$tmp = substr($_SERVER['REQUEST_URI'],$len);

	return ($tmp == false ? "" : $tmp);
}

function parseRequestURI() {
	$pageLocation = 'pages';

	if ($_SERVER['REDIRECT_STATUS'] == '200' || $_SERVER['REDIRECT_STATUS'] == null) {
		$requestURI = substr($_SERVER['REQUEST_URI'],1);
		$queryString = preg_split('/\?/',$requestURI);
		$getParams = preg_split('/\//',$queryString[0]);
		$pageAction = '';
		$pageValue = '';

		//Nothing requested - use Index by default
		if (empty($getParams[0])) {
			$getParams[0] = 'index';
		}

		//Ignore whats been set, if not authenticated, ask for login
		if ($_SESSION['authenticated'] != true
				&& $getParams[0] != 'login'
				&& $getParams[0] != 'alt_login'
				&& $getParams[0] != 'register'
				&& $getParams[0] != 'validate'
				&& $getParams[0] != 'privacy'
				&& $getParams[0] != 'terms'
				&& $getParams[0] != 'about') {
			//Set default page to logon if none of above.
			$getParams[0] = 'login';
		}
		
		$pageName = urldecode($getParams[0]);
		
		$page = $pageLocation.'/'.$pageName.'.php';

		if (array_key_exists(1,$getParams)) {
			if (!empty($getParams[1])) {
				$pageAction = urldecode($getParams[1]);
			}
		}
		if (array_key_exists(2,$getParams)) {
			if (!empty($getParams[2])) {
				$pageValue = urldecode($getParams[2]);
			}
		}
	} else {
		$page = 'pages/'.$_SERVER['REDIRECT_STATUS'].'.php';
	}
	return array(
		'page' => $page,
		'request' => $getParams[0],
		'action' => $getParams[1],
		'value' => $getParams[2],
		);
}

$_SERVER['REQUEST_URI'] = fixRequestURI();
$requestDetails = parseRequestURI();

require_once("config.php");
require_once("util/page_class.php");
require_once("util/page_factory_class.php");
require_once("util/api_class.php");

//Set second param to TRUE if you want to capture raw output from API
$api = new API($CONFIG['api'], false);

if (file_exists($requestDetails['page'])) {
        include($requestDetails['page']);
}

?>
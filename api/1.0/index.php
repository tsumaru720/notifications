<?php

session_start();

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
			return array('error' => 'Invalid API Request - Empty Request');
		}

		$methodName = urldecode($getParams[0]);
		
		$page = $pageLocation.'/'.$methodName.'.php';

		if (array_key_exists(1,$getParams)) {
			if (!empty($getParams[1])) {
				$methodAction = urldecode($getParams[1]);
			}
		}
	} else {
		return array('error' => 'Redirect status '.$_SERVER['REDIRECT_STATUS']);
	}
	return array(
		'method' => $page,
		'request' => $getParams[0],
		'action' => $getParams[1],
		);
}

$_SERVER['REQUEST_URI'] = fixRequestURI();
$requestDetails = parseRequestURI();

require_once('util/apiError.php');
require_once('util/apiInfo.php');

if ($requestDetails['error']) {
	apiError($requestDetails);
}

if (file_exists($requestDetails['method'])) {
        include($requestDetails['method']);
} else {
	apiError(array('error' => 'Invalid API Request - No such Method'));
}



?>

<?php

function apiError($message) {
	echo json_encode($message);
	die();
}

?>
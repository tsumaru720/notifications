<?php
foreach ($_SESSION as $k => $v) {
	unset($_SESSION[$k]);
}
?>
Debug mode - you have been logged out.
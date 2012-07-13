<!DOCTYPE html>
<html lang="en">
<head>
	<title>Authorize Computer</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="res/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="res/css/box.css" rel="stylesheet" type="text/css">
</head>
<body>

	<div class="container">
		<div class='row'>
			<div class='span8 offset2'>
				<div class="box">
					<div class="header">
						<h3>Authorize Computer</h3>
					</div>
					<div class="body">
						<div class="upper-form">
							<?php if (isset($messageType)) { $message->display(); } ?>
<div id="yubikey"><i class="icon-arrow-left"></i> <a href=".">Sign in with YubiKey</a></div>
<div id="no_yubikey"><a href="alt_login">Don't have a YubiKey?</a> <i class="icon-arrow-right"></i></div>
							<div class="centered">
								&nbsp;
							</div>
						</div>
					<div>
				</div>
			</div>
		</div>
		<div class="centered">
			<i class="icon-eye-open"></i> <a href="privacy">Privacy Policy</a>
			<i class="icon-info-sign"></i> <a href="terms">Terms and Conditions</a>
			<i class="icon-question-sign"></i> <a href="about">About</a>
		</div>
	</div>

<script src="res/bootstrap/js/jquery.js"></script>
<script src="res/bootstrap/js/bootstrap.js"></script>

</body>
</html>
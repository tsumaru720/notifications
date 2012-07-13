<!DOCTYPE html>
<html lang="en">
<head>
	<title>Alternate Login</title>
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
						<h3>Site Login - Alternate</h3>
					</div>
					<div class="body">
						<div class="upper-form">
							<form id="logon" method="post" onsubmit="return validateCredentials()">
							<p>If you don't have a YubiKey, you can still log into this site conventioanlly with a username and password.</p>
							<p><strong>Note</strong> If you log in via this method, you will need to validate the computer you are using via your email address before you can proceed.</p>
							<p>We will send you a validation email with instructions once you have signed in successfully.</p>
							<p>&nbsp;</p>

							<?php if (isset($messageType)) { $message->display(); } ?>
							<div class="centered">
								<div id="username-control" class="control-group">
									<input class="input-xlarge focused" autofocus="autofocus" name="username" type="text" placeholder="Username...">
								</div>
								<div id="password-control" class="control-group">
									<input class="input-xlarge" autocomplete="off" name="password" type="password" placeholder="Password...">
								</div>
								<p>&nbsp;</p>
							</div>
						</div>
						<div class="form-actions">
							<input class="btn btn-success" type="submit" value="Log in">
							<a class="btn" href="register">Sign up</a>
							</form>
							<div id="yubikey"><i class="icon-arrow-left"></i> <a href=".">Sign in with YubiKey</a></div>
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

<script src="res/js/validateCredential.js"></script>
<script src="res/bootstrap/js/jquery.js"></script>
<script src="res/bootstrap/js/bootstrap.js"></script>

</body>
</html>
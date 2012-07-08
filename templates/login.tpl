<!DOCTYPE html>
<html lang="en">
<head>
	<title>Login</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="res/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="res/css/login.css" rel="stylesheet" type="text/css">
</head>
<body>

	<div class="container">
		<div class='row'>
			<div class='span8 offset2'>
				<div class="box">
					<div class="header">
						<h3>Site Login - YubiKey</h3>
					</div>
					<div class="body">
						<div class="upper-form">
							<form id="logon" method="post" onsubmit="return validateYubikey()">
							<p>This site uses <a href="http://yubico.com">YubiKey</a> technology for authentication.</p>
							<p>Please use your key below to log in to your account. If you do not have an account, use it anyway to begin the registration process.</p>
							<p>&nbsp;</p>

							<div class="centered">
								<div id="control" class="control-group">
									<div class="input-prepend">
										<span class="add-on"><img src="res/images/yubico-icon-small.gif"></span><input class="span4" autocomplete="off" autofocus="autofocus" id="inputError" name="yubikey" type="password" placeholder="YubiKey...">
									</div>
								</div>
								<p>&nbsp;</p>
							</div>
						</div>
						<div class="form-actions">
							<input class="btn btn-success" type="submit" value="Log in">
							<div id="no_yubikey"><a href="alt_login">Don't have a YubiKey?</a> <i class="icon-arrow-right"></i></div>
							</form>
						</div>
					<div>
				</div>
			</div>
		</div>
	</div>

<script src="res/js/validateYubikey.js"></script>

</body>
</html>
function validateCredentials() {
	var username = document.forms["logon"]["username"];
	var password = document.forms["logon"]["password"];
	
	document.getElementById('username-control').className = "control-group";
	document.getElementById('password-control').className = "control-group";
	
	if (password.value == "") {
		document.getElementById('password-control').className = "control-group error";
		password.focus();
	}
	if (username.value == "") {
		document.getElementById('username-control').className = "control-group error";
		username.focus();
	}
	
	if (username.value == "" || password.value == "") {
		return false;
	}
}
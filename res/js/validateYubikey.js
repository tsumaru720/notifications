function validateYubikey() {
	var yubikey = document.forms["logon"]["yubikey"];
	
	if (yubikey.value == "") {
		document.getElementById('control').className = "control-group error";
		yubikey.placeholder = "Please use your YubiKey...";
		yubikey.focus();
		return false;
	}
	
	if (yubikey.value.length != 44) {
		yubikey.value = "";
		document.getElementById('control').className = "control-group error";
		yubikey.placeholder = "Invalid OTP...";
		yubikey.focus();
		return false;
	}
}
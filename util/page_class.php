<?php

class Page {
	private $file = '';
	private $vars = array();

	// Create the page.
	public function __construct($file) { $this->file = $file; }

	// Store varibles for the page to access
	public function setVar($name, $value) { $this->vars[$name] = $value; }

	// Display the page.
	public function display() {
		$code = file_get_contents('templates/'.$this->file);

		// Parse the variables and make them available to the page
		foreach ($this->vars as $k => $v) { if (!isset($$k)) { $$k = $v; } }

		eval('?>' . $code);
	}
}
?>
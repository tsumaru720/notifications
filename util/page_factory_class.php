<?php

class PageFactory {
	private $file = '';
	private $vars = array();

	// Store varibles for the page to access
	public function setVar($name, $value) { $this->vars[$name] = $value; }

	public function newPage($file) {
		$this->file = $file;

		$page = new Page($file);

		foreach ($this->vars as $key => $value) {
			$page->setVar($key, $value);
		}

		return $page;
	}
}
?>
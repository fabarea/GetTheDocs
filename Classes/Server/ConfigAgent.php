<?php

class ConfigAgent {


	/**
	 * @var string
	 */
	protected $extensionName = '';

	/**
	 * @var string
	 */
	protected $extensionVersion = '';

	/**
	 * @var string
	 */
	protected $possibleFiles = array('conf.py', 'Makefile');

	/**
	 * Constructor
	 */
	public function __construct() {
		// @todo find a way to get theses values dynamically
		$this->extensionName = 'Dummy';
		$this->extensionVersion = '1.0';
		$this->file = $_POST['file'];
	}

	/**
	 * Process the User request
	 *
	 * @return void
	 */
	public function work() {
		$this->check();
		$this->render();
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	protected function render() {

		// Generate configuration files
		$view = new Template("Resources/Template/ConfigLocal/$this->file");

		if ($this->file == 'conf.py') {
			$view->set('version', $this->extensionVersion);
			$view->set('extensionName', $this->extensionName);
		}
		$content = $view->fetch();
		print $content;
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	public function check() {
		if (! in_array($this->file, $this->possibleFiles)) {
			throw new Exception("Exception: unknown file request '$this->file'");
		}
	}
}

?>
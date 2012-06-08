<?php

class ConfigAgent {

	/**
	 * @var string
	 */
	protected $extensionName = '';

	/**
	 * @var string
	 */
	protected $fileName = '';

	/**
	 * @var string
	 */
	protected $extensionVersion = '';

	/**
	 * @var string
	 */
	protected $possibleFiles = array('conf.py', 'Makefile');

	/**
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * Check that the value is correct
	 *
	 * @param $parameters
	 * @throws Exception
	 * @return void
	 */
	public function check($parameters) {
		if (!in_array($parameters['file'], $this->possibleFiles)) {
			throw new Exception("Exception: unknown file request \"{$parameters['file']}\"");
		}
	}

	/**
	 * Initialize
	 *
	 * @param $parameters
	 */
	public function initialize($parameters) {

		$this->parameters = $parameters;

		// @todo find a way to get theses values dynamically
		$this->extensionName = 'Dummy';
		$this->extensionVersion = '1.0';
		$this->fileName = $this->parameters['file'];
	}

	/**
	 * Process the User request
	 *
	 * @return void
	 */
	public function process() {
		$this->render();
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	protected function render() {

		// Generate configuration files
		$view = new Template("Resources/Private/Template/ConfigAgent/$this->fileName");

		if ($this->fileName == 'conf.py') {
			$view->set('version', $this->extensionVersion);
			$view->set('extensionName', $this->extensionName);
		}
		$content = $view->fetch();
		print $content;
	}
}

?>
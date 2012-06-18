<?php

class ServerConfig {

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
	 * Constructor
	 *
	 * @param $parameters
	 * @return void
	 */
	public function __construct($parameters) {
		$this->parameters = $parameters;
	}

	/**
	 * Process the User request
	 *
	 * @return void
	 */
	public function process() {
		$this->check();
		$this->initialize();
		$this->render();
	}

	/**
	 * Check that the value is correct
	 *
	 * @throws Exception
	 * @return void
	 */
	protected  function check() {
		if (!in_array($this->parameters['file'], $this->possibleFiles)) {
			throw new Exception("Exception: unknown file request \"{$this->parameters['file']}\"");
		}
	}

	/**
	 * Initialize
	 *
	 */
	protected function initialize() {

		// @todo find a way to get theses values dynamically
		$this->extensionName = 'Dummy';
		$this->extensionVersion = '1.0';
		$this->fileName = $this->parameters['file'];
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	protected function render() {

		// Generate configuration files
		$view = new Template("Resources/Private/Template/ServerConfig/$this->fileName");

		if ($this->fileName == 'conf.py') {
			$view->set('version', $this->extensionVersion);
			$view->set('extensionName', $this->extensionName);
		}
		Output::write($view->fetch());
	}
}

?>
<?php

/**
 * Worker class to make the job done!
 */
class ClientConvert {

	/**
	 * @var string
	 */
	protected $manual = '';

	/**
	 * @var string
	 */
	protected $file = 'Documentation.zip';

	/**
	 * @var boolean
	 */
	protected $debug = FALSE;

	/**
	 * The arguments from the console
	 *
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	public function __construct($arguments = array()) {
		$this->arguments = Console::parseArgs($arguments);

		if (! empty($this->arguments[0])) {
			$this->manual = $this->arguments[0];
		}

		if (isset($this->arguments['debug'])) {
			$this->debug = TRUE;
		}
	}

	/**
	 * Do the job
	 */
	public function work() {
		if (isset($this->arguments['h']) || isset($this->arguments['help']) || count($this->arguments) == 0) {
			$this->displayUsage();
		}
		$this->checkEnvironment();

		$data = array();
		$data['action'] = 'convert';
		$data['user_workspace'] = USER_WORKSPACE;
		$data['debug'] = $this->debug;
		$data['api_version'] = API_VERSION;
		$files = array();
		$files['manual'] = array(
			'path' => $this->manual,
			'name' => 'manual.sxw'
		);

		$content = Request::post(HOST, $data, $files);
		Console::output($content);
		#$result = file_put_contents("$this->directory/$this->file", $content);
		#if ($result === FALSE) {
		#	throw new Exception("Exception: file '$this->file' was not written");
		#}
		#Console::output("File \"$this->file\" written");
	}

	/**
	 * Check if the environment is runnable
	 */
	protected function checkEnvironment() {

		// Test if directory given as input is correct
		if (!is_file($this->manual)) {
			$this->displayError("file does not exist! Make sure to point to a manual.sxw: \"$this->manual\"");
		}
	}

	/**
	 * Output a usage message on the console
	 *
	 * @return void
	 */
	protected function displayError($message) {
		$message = <<< EOF
Something went wrong...

Fatal: $message
EOF;
		Console::output($message);
		die();
	}

	/**
	 * Output a usage message on the console
	 *
	 * @return void
	 */
	protected function displayUsage() {
		$message = <<< EOF
Convert legacy OpenOffice documentation to reST.

Usage:
	get-the-docs convert FILE [OPTIONS]

	where "FILE" points to a manual.sxw

Options:
	-h, --help             Display this help message
EOF;
		Console::output($message);
		die();
	}
}

?>
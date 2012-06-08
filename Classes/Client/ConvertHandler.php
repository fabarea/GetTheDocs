<?php

/**
 * Worker class to make the job done!
 */
class ConvertHandler {

	/**
	 * @var string
	 */
	protected $directory = '';

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
	protected $verbose = FALSE;

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
		if (!empty($this->arguments[1])) {
			$this->directory = rtrim($this->arguments[1], '/');
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
		$data['username'] = USERNAME;
		$files = array();
		$files['manual'] = array(
			'path' => $this->manual,
			'name' => 'manual.sxw'
		);

		$content = Request::post(HOST, $data, $files);
		print_r("$content");
//		$result = file_put_contents("$this->directory/$this->file", $content);
//
//		if ($result === FALSE) {
//			throw new Exception("Exception: file '$this->file' was not written");
//		}
		Console::output("File \"$this->file\" written");
	}

	/**
	 * Check if the environment is runnable
	 */
	protected function checkEnvironment() {

		// Test if directory given as input is correct

		if (! is_dir($this->directory)) {
			$this->displayError("directory does not exist! Make sure to give a valid path \"" . $this->directory . '"');
		}

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
	get-the-docs convert FILE PATH [OPTIONS]

	FILE points to a manual.sxw
	PATH is a directory where a ZIP file will be created containing the reST documentation

Options:
	-h, --help             Display this help message
EOF;
		Console::output($message);
		die();
	}
}

?>
<?php
/**
 * Worker class to make the job done!
 */
class ClientConfig {

	/**
	 * @var string
	 */
	protected $directory = '';

	/**
	 * @var boolean
	 */
	protected $force = FALSE;

	/**
	 * @var array
	 */
	protected $files = array('conf.py', 'Makefile');

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

		if (!empty($this->arguments[0])) {
			$this->directory = rtrim($this->arguments[0], '/');
		}

		if (isset($this->arguments['f']) || isset($this->arguments['force'])) {
			$this->force = TRUE;
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
		$data['action'] = 'config';
		$data['api_version'] = API_VERSION;

		foreach ($this->files as $file) {
			$data['file'] = $file;
			$content = trim(Request::post(HOST, $data));

			if (!$content) {
				throw new Exception("Exception: content empty for '$file'");
			}

			$result = file_put_contents("$this->directory/$file", $content);
			if ($result === FALSE) {
				throw new Exception("Exception: file '$file' was not written");
			}
			Console::output("File \"$file\" written");
		}
	}

	/**
	 * Check if the environment is runnable
	 */
	protected function checkEnvironment() {

		// Test if directory given as input is correct
		if (! is_dir($this->directory)) {
			$this->displayError("directory does not exist! Make sure to give a valid path \"" . $this->directory . '"');
		}

		// Display warning message
		$existingFiles = '';
		foreach ($this->files as $file) {
			if (file_exists("$this->directory/$file")) {
				$existingFiles .= "- $file\n";
			}
		}


		if ($existingFiles && ! $this->force) {
			$message = <<< EOF
You are going to overwrite files:

$existingFiles
Aye you sure of that?\nPress y or n:
EOF;

			Console::output($message);

			$reply = strtolower(trim(fgets(STDIN)));
			if ($reply !== 'y' && $reply !== 'yes') {
				die();
			}
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
Fetch configuration for local rendering. Two files are going to be written: conf.py and Makefile.

Usage:
	get-the-docs config PATH [OPTIONS]

	where "PATH" is a directory where configuration will be written

Options:
	-f, --force            Overwrite files without asking
	-h, --help             Display this help message
EOF;
		Console::output($message);
		die();
	}
}

?>
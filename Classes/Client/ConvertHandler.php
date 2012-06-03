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
	protected $documentationDirectory = '';

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

//		if (! empty($this->arguments[0])) {
//			$this->directory = rtrim($this->arguments[0], '/');
//			$this->documentationDirectory = $this->directory . '/Documentation';
//		}
//
//		if (isset($this->arguments['v']) || isset($this->arguments['verbose'])) {
//			$this->verbose = TRUE;
//		}
//
//		if (isset($this->arguments['d']) || isset($this->arguments['dry-run'])) {
//			Console::$dryRun = TRUE;
//		}
//
//		if (isset($this->arguments['v']) || isset($this->arguments['verbose'])) {
//			Console::$verbose = TRUE;
//		}
	}

	/**
	 * Do the job
	 */
	public function work() {
		if (isset($this->arguments['h']) || isset($this->arguments['help']) || count($this->arguments) == 0) {
			$this->displayUsage();
		}

		print ('Implementation is coming...');
		exit();

		$this->checkEnvironment();

		// Execute commands
		Console::execute($commands);
	}

	/**
	 * Check if the environment is runnable
	 */
	protected function checkEnvironment() {

		// Test if directory given as input is correct
//		if (! is_dir($this->directory)) {
//			$this->displayError("directory does not exist! Make sure to give a valid path \"" . $this->directory . '"');
//		}
//
//		if (!is_dir($this->documentationDirectory)) {
//			$this->displayError("documentation folder does not exist! Are you sure it is a TYPO3 extension? Path wanted: \"" . $this->documentationDirectory . '"');
//		}
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
Con

Usage:
	get-the-docs convert PATH            convert manual.sxw to reST documentation

Options:
	-d, --dry-run          Output command that are going to be executed but don't run them
	-h, --help             Display this help message

EOF;
		Console::output($message);
		die();
	}
}

?>
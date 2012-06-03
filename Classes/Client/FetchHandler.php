<?php
/**
 * Worker class to make the job done!
 */
class FetchHandler {

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

		// Computes command to be run
		$extensionName = basename($this->directory);
		$tmpFile = '/tmp/' . $extensionName . '.zip';
		$commands[] = 'echo "Generating archive..."';
		$commands[] = "zip -rq $tmpFile $this->directory --include $this->documentationDirectory/\*.rst";
		$commands[] = "zip -rq $tmpFile $this->directory --include $this->documentationDirectory/Images/*";

		// Add also ext_emconf.php to extract data from there
		if (is_file("$this->directory/ext_emconf.php")) {
			$commands[] = "zip -rq $tmpFile $this->directory --include $this->directory/ext_emconf.php";
		}
		$commands[] = 'echo "Sending to server..."';
		$host = rtrim(HOST, '/');
		$commands[] = "curl -k -s -F archive=@" . $tmpFile . " -F 'username=" . USERNAME . ";type=text/foo' " . $host . '/';
		$commands[] = "rm -f " . $tmpFile;

		// Execute commands
		Console::execute($commands);
	}

	/**
	 * Check if the environment is runnable
	 */
	protected function checkEnvironment() {

		// Test if directory given as input is correct

		if (! is_dir($this->directory)) {
			$this->displayError("directory does not exist! Make sure to give a valid path \"" . $this->directory . '"');
		}

		if (!is_dir($this->documentationDirectory)) {
			$this->displayError("documentation folder does not exist! Are you sure it is a TYPO3 extension? Path wanted: \"" . $this->documentationDirectory . '"');
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
	#get-the-docs fetch pdf (options)            download the PDF version
	#get-the-docs fetch html (options)           download the HTML version
		$message = <<< EOF
Download files related to documentation

Usage:
	get-the-docs fetch configuration (options)  download the necessary files for generating the docs locally

Options:
	-d, --dry-run          Output command that are going to be executed but don't run them
	-h, --help             Display this help message

EOF;
		Console::output($message);
		die();
	}
}

?>
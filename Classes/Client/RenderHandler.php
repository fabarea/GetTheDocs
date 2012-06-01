<?php
/*                                                                        *
 * This script belongs to the PLEASE package                              *
 * https://github.com/gebruederheitz/PLEASE                               *
 *                                                                        */

/**
 * Worker class to make the job done!
 */
class RenderHandler {

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

		if (! empty($this->arguments[0])) {
			$this->directory = rtrim($this->arguments[0], '/');
			$this->documentationDirectory = $this->directory . '/Documentation';
		}

//		if (!empty($this->arguments[1])) {
//			$target = rtrim($this->arguments[1], '/');
//			$this->target = BASE_PATH . '/' . $target;
//			$this->databaseTarget = str_replace(array('/', '-', '.'), '_', $target);
//
//			$targetParts = explode('/', $this->arguments[1]);
//			$targetParts = array_reverse($targetParts);
//			$this->domainTarget = implode('.', $targetParts);
//		}

		if (isset($this->arguments['v']) || isset($this->arguments['verbose'])) {
			$this->verbose = TRUE;
		}

		if (isset($this->arguments['d']) || isset($this->arguments['dry-run'])) {
			Console::$dryRun = TRUE;
		}

		if (isset($this->arguments['v']) || isset($this->arguments['verbose'])) {
			Console::$verbose = TRUE;
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

		// Computes command to be run
		$extensionName = basename($this->directory);
		$tmpFile = '/tmp/' . $extensionName . '.zip';
		$commands[] = 'echo "Generating archive..."';
		$commands[] = "cd $this->directory; zip -rq $tmpFile . --include Documentation/\*.rst";

		// Add possible images into the Zip
		if (is_dir("$this->directory/Documentation/Images/")) {
			$commands[] = "cd $this->directory; zip -rq $tmpFile . --include Documentation/Images/*";
		}

		// Add also ext_emconf.php to use as source of information on the server
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
		$message = <<< EOF
Render documentation on-line

Usage:
	get-the-docs render PATH

	where "PATH" points to TYPO3 extension

Options:
	-v, --verbose          Increase verbosity
	-d, --dry-run          Output command that are going to be executed but don't run them
	-h, --help             Display this help message

EOF;
		Console::output($message);
		die();
	}
}

?>
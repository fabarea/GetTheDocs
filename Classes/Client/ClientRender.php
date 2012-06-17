<?php
/**
 * Worker class to make the job done!
 */
class ClientRender {

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
	protected $debug = FALSE;

	/**
	 * @var string
	 */
	protected $makeZip = '';

	/**
	 * The formats
	 *
	 * @var array
	 */
	protected $formats = array();

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

		if (isset($this->arguments['d']) || isset($this->arguments['dry-run'])) {
			Console::$dryRun = TRUE;
		}

		if (isset($this->arguments['debug'])) {
			$this->debug = TRUE;
		}

		if (isset($this->arguments['v']) || isset($this->arguments['verbose'])) {
			Console::$verbose = TRUE;
		}

		if (isset($this->arguments['json'])) {
			$this->formats[] = 'json';
		}

		if (isset($this->arguments['gettext'])) {
			$this->formats[] = 'gettext';
		}

		if (isset($this->arguments['pdf'])) {
			$this->formats[] = 'pdf';
		}

		if (isset($this->arguments['epub'])) {
			$this->formats[] = 'epub';
		}

		if (isset($this->arguments['html']) || empty($this->formats)) {
			$this->formats[] = 'html';
		}

		if (isset($this->arguments['zip'])) {
			$this->makeZip = 'zip';
		}
	}

	/**
	 * Get a list of files
	 *
	 * @return array
	 */
	protected function getFiles() {

		$files = array();

		// Get all reST files
		$directory = new RecursiveDirectoryIterator("$this->directory");
		$filter = new DirectoryFilter($directory, '/^(?!\..*|_.*)/'); // Filter out folders
		$filter = new FileFilter($filter, '/\.(?:rst)$/'); // Filter files
		$iterator = new RecursiveIteratorIterator($filter);
		foreach ($iterator as $key => $file) {
			$files[] = $key;
		}

		// Get all images
		$directory = new RecursiveDirectoryIterator(realpath("$this->directory/Documentation"));
		$filter = new DirectoryFilter($directory, '/^(?!\..*|_.*)/'); // Filter out folders
		$filter = new FileFilter($filter, '/\.(?:jpg|gif|png|jpeg)$/'); // Filter files
		$iterator = new RecursiveIteratorIterator($filter);
		foreach ($iterator as $key => $file) {
			$files[] = $key;
		}

		// Add also ext_emconf.php to use as source of information on the server
		if (is_file("$this->directory/ext_emconf.php")) {
			$files[] = "$this->directory/ext_emconf.php";
		}
		return $files;
	}

	/**
	 * Create an archive
	 *
	 * @param $files
	 * @throws Exception
	 * @return array
	 */
	protected function makeArchive($files) {

		Console::output("Generating zip file...");

		// create object
		$zip = new ZipArchive();

		$tempFile = tempnam('/tmp', 't3')  . '.zip';
		if (!$zip->open($tempFile, ZIPARCHIVE::OVERWRITE)) {
			die("Failed to create zip file" . PHP_EOL);

		}

		foreach ($files as $file) {
			$absolutePath = realpath($file);
			$parts = explode($this->directory, $absolutePath);
			if (! empty ($parts[1])) {
				$relativePath = substr($parts[1], 1);
				$result = $zip->addFile($absolutePath, $relativePath);
				if (! $result) {
					throw new Exception("Could not add file: $absolutePath");
				}
			}
		}

		// Test status
		if (!$zip->status == ZIPARCHIVE::ER_OK) {
			echo "Failed to write files to zip" . PHP_EOL;
		}
		$zip->close();

		$zipFile = array(
			'path' => $tempFile,
			'name' => basename($this->directory) . '.zip',
		);

		return $zipFile;
	}

	/**
	 * Send archive to the server
	 *
	 * @param $zipFile
	 */
	protected function send($zipFile) {
		Console::output("Sending to server...");

		$data = array();
		$data['action'] = 'render';
		$data['user_workspace'] = USER_WORKSPACE;
		$data['doc_workspace'] = str_replace('.zip', '', $zipFile['name']);
		$data['doc_name'] = $zipFile['name'];
		$data['format'] = implode(',', $this->formats);
		$data['make_zip'] = $this->makeZip;
		$data['debug'] = $this->debug ? 1 : 0;
		$data['api_version'] = API_VERSION;
		$files = array();
		$files['zip_file'] = array(
			'path' => $zipFile['path'],
			'name' => $zipFile['name'],
		);

		$content = Request::post(HOST, $data, $files);
		Console::output($content);
	}

	/**
	 * Do the job
	 */
	public function work() {
		if (isset($this->arguments['h']) || isset($this->arguments['help']) || count($this->arguments) == 0) {
			$this->displayUsage();
		}

		$this->checkEnvironment();
		$files = $this->getFiles();
		$zipFile = $this->makeArchive($files);
		$this->send($zipFile);

		// Clean up temp file
		unlink($zipFile['path']);
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
	#-v, --verbose          Increase verbosity
		$message = <<< EOF
Render documentation on-line

Usage:
	get-the-docs render PATH [OPTIONS]

	where "PATH" points to TYPO3 extension

Options:
	--html                  Render HTML version (implicit option if no other format option given)
	--json                  Render JSON version
	--gettext               Render GetText version
	--epub                  Render ePub version
	--pdf                   Render PDF version (not yet implemented!)
	--zip                   Make a ZIP of the generated documentation
	--fetch                 Download what has been rendered (not yet implemented!)
	-d, --dry-run           Output command that are going to be executed but don't run them
	-h, --help              Display this help message

EOF;
		Console::output($message);
		die();
	}
}

?>
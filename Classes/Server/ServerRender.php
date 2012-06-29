<?php

class ServerRender {

	/**
	 * @var string
	 */
	protected $uploadDirectory = '';

	/**
	 * @var string
	 */
	protected $homeDirectory = '';

	/**
	 * @var string
	 */
	protected $userWorkspace = '';

	/**
	 * @var string
	 */
	protected $userWorkspacePath = '';

	/**
	 * @var string
	 */
	protected $userWorkspacePathAbsolute = '';

	/**
	 * @var string
	 */
	protected $docWorkspace = '';

	/**
	 * @var string
	 */
	protected $docWorkspacePath = '';

	/**
	 * @var string
	 */
	protected $docWorkspacePathAbsolute = '';

	/**
	 * @var string
	 */
	protected $warningsFile = '';

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
	protected $fileName = '';

	/**
	 * The file uploaded
	 *
	 * @var array
	 */
	protected $file = array();

	/**
	 * @var string
	 */
	protected $url = '';

	/**
	 * @var string
	 */
	protected $makeZip = '';

	/**
	 * @var array
	 */
	protected $formats = array();

	/**
	 * @var array
	 */
	protected $allowedFormats = array('html', 'json', 'gettext', 'epub');

	/**
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * Constructor
	 *
	 * @param $parameters
	 * @param $files
	 * @return void
	 */
	public function __construct($parameters, $files) {
		$this->parameters = $parameters;
		if (!empty($files['zip_file'])) {
			$this->file = $files['zip_file'];
		}
	}

	/**
	 * Process the User request
	 *
	 * @return void
	 */
	public function process() {
		$this->check();
		$this->initialize();
		$this->prepare();
		$this->unPack();
		$this->render();
		$this->displayFeedback();
		$this->cleanUp();
	}

	/**
	 * Check that the value is correct
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function check() {
		if (empty($this->file) || $this->file['error'] != 0) {
			throw new Exception('missing zip file');
		}

		if ($this->parameters['user_workspace'] == '') {
			throw new Exception('missing user_workspace parameter');
		}

		if ($this->parameters['doc_name'] == '') {
			throw new Exception('missing doc_name parameter');
		}

		if ($this->parameters['doc_workspace'] == '') {
			throw new Exception('missing doc_workspace parameter');
		}
	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	protected function initialize() {

		// Get file name value without extension
		$fileNameWithExtension = $this->parameters['doc_name'];
		$fileInfo = pathinfo($fileNameWithExtension);
		$this->fileName = $fileInfo['filename'];

		$this->extensionName = $this->fileName;
		$this->extensionVersion = '1.0';

		// Computes user workspace
		$this->userWorkspace = $this->parameters['user_workspace'];
		$this->docWorkspace = $this->parameters['doc_workspace'];

		// Computes some needed properties
		$this->homeDirectory = dirname($_SERVER['SCRIPT_FILENAME']);
		$this->uploadDirectory = "$this->homeDirectory/" . UPLOAD_DIRECTORY . "/$this->userWorkspace/$this->docWorkspace";
		$this->userWorkspacePath = FILES_DIRECTORY . "/$this->userWorkspace";
		$this->userWorkspacePathAbsolute = "$this->homeDirectory/$this->userWorkspacePath";
		$this->docWorkspacePath = "$this->userWorkspacePath/$this->docWorkspace";
		$this->docWorkspacePathAbsolute = "$this->userWorkspacePathAbsolute/$this->docWorkspace";

		$this->warningsFile = "$this->uploadDirectory/Warnings.txt";
		$this->url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['PHP_SELF']);

		// Define formats to be generated
		$formats = explode(',', $this->parameters['format']);
		foreach ($formats as $format) {
			if (in_array($format, $this->allowedFormats)) {
				$this->formats[] = $format;
			}
		}

		// Define whether to make a zip file after rendering the doc
		if ($this->parameters['make_zip'] == 'zip') {
			$this->makeZip = 'zip';
		}
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	protected function render() {

		// Generate configuration files
		$view = new Template('Resources/Private/Template/ServerRender/conf.py');
		$view->set('version', $this->extensionVersion);
		$view->set('extensionName', $this->extensionName);
		$content = $view->fetch();
		file_put_contents($this->uploadDirectory . '/conf.py', $content);

		$view = new Template('Resources/Private/Template/ServerRender/Makefile');
		$view->set('buildDirectory', "$this->docWorkspacePathAbsolute");
		$content = $view->fetch();
		file_put_contents($this->uploadDirectory . '/Makefile', $content);

		$commands = array();
		// First clean directory
		$commands[] = "cd $this->uploadDirectory; make clean --quiet;";

		foreach ($this->formats as $format) {
			$commands[] = "cd $this->uploadDirectory; make $format --quiet 2> Warnings.txt;";
		}
		$commands[] = "cd $this->uploadDirectory; make latex --quiet;";

		if ($this->makeZip == 'zip') {
			$commands[] = "cd $this->docWorkspacePathAbsolute/..; zip -qr $this->docWorkspace.zip $this->docWorkspace";
		}

		Command::execute($commands);
	}

	/**
	 * Create directory
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function prepare() {
		$directories = array($this->uploadDirectory, $this->docWorkspacePathAbsolute);
		foreach ($directories as $directory) {
			if (!is_dir($directory)) {
				$result = mkdir($directory, 0755, TRUE);

				if ($result === FALSE) {
					throw new Exception('Exception: directory not created on the server "' . $directory . '"');
				}
			}
		}
	}

	/**
	 * Unzip zip file
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function unPack() {
		$zip = new \ZipArchive();
		$res = $zip->open($this->file['tmp_name']);
		if ($res === TRUE) {
			$zip->extractTo($this->uploadDirectory);
			$zip->close();
		} else {
			throw new Exception('Exception: something when wrong with the zip file');
		}
	}

	/**
	 * Clean up environment
	 *
	 * @return void
	 */
	protected function cleanUp() {
		// Remove directory
		File::removeDirectory($this->uploadDirectory);
	}

	/**
	 * Unzip zip file
	 *
	 * @return void
	 */
	protected function displayFeedback() {

		$rendered = '';
		foreach ($this->formats as $format) {
			if ($format == 'html') {
				$rendered .= "HTML docs:\n";
				$rendered .= "$this->url$this->docWorkspacePath\n\n";
			} elseif ($format == 'json') {
				$rendered .= "JSON docs:\n";
				$rendered .= "$this->url$this->docWorkspacePath/json\n\n";
			} elseif ($format == 'gettext') {
				$rendered .= "GetText docs:\n";
				$rendered .= "$this->url$this->docWorkspacePath/local\n\n";
			} elseif ($format == 'epub') {
				$rendered .= "ePub docs:\n";
				$rendered .= "$this->url$this->docWorkspacePath/epub\n\n";
			}
		}

		if ($this->makeZip == 'zip') {
			$rendered .= "Zip file to download:\n";
			$rendered .= "$this->url$this->docWorkspacePath.zip\n\n";
		}

		$warnings = '';
		if (file_exists($this->warningsFile)) {
			$content = trim(file_get_contents($this->warningsFile));
			if ($content) {
				$warnings = "\nFollowing warnings have been detected:\n\n";
				$warnings .= file_get_contents($this->warningsFile);
			}
		}

		$content = <<< EOF

$rendered
Notice, generated files are automatically removed after a grace period!
$warnings

EOF;

		Output::write($content);
	}
}

?>
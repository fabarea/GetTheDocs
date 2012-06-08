<?php

class RenderAgent {

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
	protected $userDirectory = '';

	/**
	 * @var string
	 */
	protected $buildDirectory = '';

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
	 * @var string
	 */
	protected $url = '';

	/**
	 * @var string
	 */
	protected $archive = '';

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
	 * Check that the value is correct
	 *
	 * @param $parameters
	 * @param $files
	 * @throws Exception
	 * @return void
	 */
	public function check($parameters, $files) {
		if (empty($files['archive']) || $files['archive']['error'] != 0) {
			throw new Exception('missing file archive');
		}

		if ($parameters['username'] == '') {
			throw new Exception('incomplete data');
		}
	}

	/**
	 * Initialize
	 *
	 * @param $parameters
	 * @param $files
	 * @return void
	 */
	public function initialize($parameters, $files) {

		$this->parameters = $parameters;

		// Get file name value without extension
		$fileNameWithExtension = $_FILES['archive']['name'];
		$fileInfo = pathinfo($fileNameWithExtension);
		$this->fileName = $fileInfo['filename'];

		$this->extensionName = $this->fileName;
		$this->extensionVersion = '1.0';

		// Get user workspace
		$username = !empty($this->parameters['username']) ? $this->parameters['username'] : '';

		#$identifier = str_shuffle(uniqid(TRUE)); // not used for now... possible random number

		// Computes property
		$this->homeDirectory = dirname($_SERVER['SCRIPT_FILENAME']);
		$this->userDirectory = UPLOAD . "/$username";
		$this->uploadDirectory = UPLOAD . "/$username/$this->fileName";
		$this->buildDirectory = FILES . "/$username/$this->fileName";
		$this->warningsFile = "$this->uploadDirectory/Warnings.txt";
		$this->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// Define rendered formats
		$formats = explode(',', $this->parameters['format']);
		foreach ($formats as $format) {
			if (in_array($format, $this->allowedFormats)) {
				$this->formats[] = $format;
			}
		}
		// Define whether to zip
		if ($this->parameters['archive'] == 'zip') {
			$this->archive = 'zip';
		}
	}

	/**
	 * Process the User request
	 *
	 * @return void
	 */
	public function process() {
		$this->prepare();
		$this->unPack();
		$this->render();
		$this->displayFeedback();
		$this->cleanUp();
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	protected function render() {

		// Generate configuration files
		$view = new Template('Resources/Private/Template/RenderAgent/conf.py');
		$view->set('version', $this->extensionVersion);
		$view->set('extensionName', $this->extensionName);
		$content = $view->fetch();
		file_put_contents($this->uploadDirectory . '/conf.py', $content);

		$view = new Template('Resources/Private/Template/RenderAgent/Makefile');
		$view->set('buildDirectory', "$this->homeDirectory/$this->buildDirectory");
		$content = $view->fetch();
		file_put_contents($this->uploadDirectory . '/Makefile', $content);

		$commands = array();
		// First clean directory
		$commands[] = "cd $this->homeDirectory/$this->uploadDirectory; make clean --quiet;";

		foreach ($this->formats as $format) {
			$commands[] = "cd $this->homeDirectory/$this->uploadDirectory; make $format --quiet;";
		}
			$commands[] = "cd $this->homeDirectory/$this->uploadDirectory; make latex --quiet;";

		if ($this->archive == 'zip') {
			$commands[] = "cd $this->homeDirectory/$this->buildDirectory/..; zip -qr $this->fileName.zip $this->fileName";
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
		$directories = array($this->uploadDirectory, $this->buildDirectory);
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
	 * Unzip archive
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function unPack() {
		$zip = new \ZipArchive();
		$res = $zip->open($_FILES['archive']['tmp_name']);
		if ($res === TRUE) {
			$zip->extractTo($this->uploadDirectory);
			$zip->close();
		} else {
			throw new Exception('Exception: something when wrong with the archive');
		}
	}

	/**
	 * Clean up environment
	 *
	 * @return void
	 */
	protected function cleanUp() {
		// Remove directory
		File::removeDirectory($this->userDirectory);
	}

	/**
	 * Unzip archive
	 *
	 * @return void
	 */
	protected function displayFeedback() {

		$rendered = '';
		foreach ($this->formats as $format) {
			if ($format == 'html') {
				$rendered .= "HTML docs:\n";
				$rendered .= "$this->url$this->buildDirectory\n\n";
			} elseif ($format == 'json') {
				$rendered .= "JSON docs:\n";
				$rendered .= "$this->url$this->buildDirectory/json\n\n";
			} elseif ($format == 'gettext') {
				$rendered .= "GetText docs:\n";
				$rendered .= "$this->url$this->buildDirectory/local\n\n";
			} elseif ($format == 'epub') {
				$rendered .= "ePub docs:\n";
				$rendered .= "$this->url$this->buildDirectory/epub\n\n";
			}
		}

		if ($this->archive == 'zip') {
			$rendered .= "Zip archive to download:\n";
			$rendered .= "$this->url$this->buildDirectory.zip\n\n";
		}

		$warnings = '';
		if (file_exists($this->warningsFile)) {
			$warnings = "Following warnings have been detected:\n";
			$warnings .= file_get_contents($this->warningsFile);
		}

		$content = <<< EOF

$rendered
Notice, the docs is kept on-line for a limited time (few days)!
$warnings
EOF;

		print $content;
	}
}

?>
<?php

class ConvertAgent {

	/**
	 * @var string
	 */
	protected $uploadDirectory = '';
//
//	/**
//	 * @var string
//	 */
//	protected $homeDirectory = '';
//
//	/**
//	 * @var string
//	 */
//	protected $userDirectory = '';
//
//	/**
//	 * @var string
//	 */
//	protected $buildDirectory = '';
//
//	/**
//	 * @var string
//	 */
//	protected $fileName = '';
//
//	/**
//	 * @var string
//	 */
//	protected $url = '';
//
//	/**
//	 * @var string
//	 */
//	protected $archive = '';
//
	/**
	 * @var array
	 */
	protected $file = array();

	/**
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * Check values are correct
	 *
	 * @param $parameters
	 * @param $files
	 * @throws Exception
	 * @return void
	 */
	public function check($parameters, $files) {
		if (empty($files['manual']) || $files['manual']['error'] != 0) {
			throw new Exception('missing file manual');
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
		$this->file = $files['manual'];

		$this->uploadDirectory = UPLOAD . "/{$this->parameters['username']}/tmp";

//		// Configuration
//
//		// Get file name value without extension
//		$fileNameWithExtension = $_FILES['archive']['name'];
//		$fileInfo = pathinfo($fileNameWithExtension);
//		$this->fileName = $fileInfo['filename'];
//
//		// Get user workspace
//		$username = !empty($_POST['username']) ? $_POST['username'] : '';
//
//		#$identifier = str_shuffle(uniqid(TRUE)); // not used for now... possible random number
//
//		// Computes property
//		$this->homeDirectory = dirname($_SERVER['SCRIPT_FILENAME']);
//		$this->userDirectory = "$uploadDirectory/$username";
//		$this->buildDirectory = "$filesDirectory/$username/$this->fileName";
//		$this->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//
//		// Define rendered formats
//		$formats = explode(',', $_POST['format']);
//		foreach ($formats as $format) {
//			if (in_array($format, array('html', 'json', 'gettext'))) {
//				$this->formats[] = $format;
//			}
//		}
//		// Define whether to zip
//		if ($_POST['archive'] == 'zip') {
//			$this->archive = 'zip';
//		}
	}

	/**
	 * Process the User request
	 *
	 * @return void
	 */
	public function process() {
		$this->prepare();
		$this->render();
//		$this->displayFeedback();
//		$this->cleanUp();
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	protected function render() {

//		// Generate configuration files
//		$view = new Template('Resources/Template/conf.py');
//		$view->set('version', $this->extensionVersion);
//		$view->set('extensionName', $this->extensionName);
//		$content = $view->fetch();
//		file_put_contents($this->uploadDirectory . '/conf.py', $content);
//
//		$view = new Template('Resources/Template/Makefile');
//		$view->set('buildDirectory', "$this->homeDirectory/$this->buildDirectory");
//		$content = $view->fetch();
//		file_put_contents($this->uploadDirectory . '/Makefile', $content);
//
//		$commands = array();
//		// First clean directory
//		$commands[] = "cd $this->homeDirectory/$this->uploadDirectory; make clean --quiet;";
//
//		foreach ($this->formats as $format) {
//			$commands[] = "cd $this->homeDirectory/$this->uploadDirectory; make $format --quiet;";
//		}
//
//		if ($this->archive == 'zip') {
//			$commands[] = "cd $this->homeDirectory/$this->buildDirectory/..; zip -qr $this->fileName.zip $this->fileName";
//		}
//
//		Command::execute($commands);
	}

	/**
	 * Prepare environment
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function prepare() {
		$directories = array($this->uploadDirectory);
		foreach ($directories as $directory) {
			if (!is_dir($directory)) {
				$result = mkdir($directory, 0755, TRUE);

				if ($result === FALSE) {
					throw new Exception('Exception: directory not created on the server "' . $directory . '"');
				}
			}
		}

		// Move file
		$result = move_uploaded_file($this->file['tmp_name'], "$this->uploadDirectory/manual.sxw");
		if (!$result) {
			throw new Exception('File not uploaded correctly');
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
				$rendered .= "HTML documentation:\n";
				$rendered .= "$this->url$this->buildDirectory\n\n";
			} elseif ($format == 'json') {
				$rendered .= "JSON documentation:\n";
				$rendered .= "$this->url$this->buildDirectory/json\n\n";
			} elseif ($format == 'gettext') {
				$rendered .= "GetText documentation:\n";
				$rendered .= "$this->url$this->buildDirectory/local\n\n";
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
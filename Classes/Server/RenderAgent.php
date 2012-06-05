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
	protected $url = '';

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// Configuration
		$uploadDirectory = 'upload';
		$filesDirectory = 'files';

		// Get file name value without extension
		$fileNameWithExtension = $_FILES['archive']['name'];
		$fileInfo = pathinfo($fileNameWithExtension);
		$fileName = $fileInfo['filename'];

		$this->extensionName = $fileName;
		$this->extensionVersion = '1.0';

		// Get user workspace
		$username = !empty($_POST['username']) ? $_POST['username'] : '';

		#$identifier = str_shuffle(uniqid(TRUE)); // not used for now... possible random number

		// Computes property
		$this->homeDirectory = dirname($_SERVER['SCRIPT_FILENAME']);
		$this->userDirectory = "$uploadDirectory/$username";
		$this->uploadDirectory = "$uploadDirectory/$username/$fileName";
		$this->buildDirectory = "$filesDirectory/$username/$fileName";
		$this->warningsFile = "$this->uploadDirectory/Warnings.txt";
		$this->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Process the User request
	 *
	 * @return void
	 */
	public function work() {
		$this->check();
		$this->prepare();
		$this->unPack();
		$this->renderDocs();
		$this->displayFeedback();
		$this->cleanUp();
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	public function renderDocs() {

		// Generate configuration files
		$view = new Template('Resources/Template/conf.py');
		$view->set('version', $this->extensionVersion);
		$view->set('extensionName', $this->extensionName);
		$content = $view->fetch();
		file_put_contents($this->uploadDirectory . '/conf.py', $content);

		$view = new Template('Resources/Template/Makefile');
		$view->set('buildDirectory', "$this->homeDirectory/$this->buildDirectory");
		$content = $view->fetch();
		file_put_contents($this->uploadDirectory . '/Makefile', $content);


		$commands = array();
		$commands[] = "cd $this->homeDirectory/$this->uploadDirectory; make clean --quiet; make html --quiet";

		Command::execute($commands);
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	public function check() {
		if (empty($_FILES['archive']) ||
			$_FILES['archive']['error'] != 0 ||
			$_POST['username'] == ''
		) {
			throw new Exception('Exception: incomplete data');
		}
	}

	/**
	 * Create directory
	 *
	 * @return void
	 */
	public function prepare() {
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
	 * @return void
	 */
	public function unPack() {
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
	public function cleanUp() {
		// Remove directory
		File::removeDirectory($this->userDirectory);
	}

	/**
	 * Unzip archive
	 *
	 * @return void
	 */
	public function displayFeedback() {

//Download HTML version:
//
//Download PDF:

		$warnings = '';
		if (file_exists($this->warningsFile)) {
			$warnings = "Following warnings have been detected:\n";
			$warnings .= file_get_contents($this->warningsFile);


		}

		$content = <<< EOF

Documentation has been generated successfully!

Read documentation on-line:
{$this->url}{$this->buildDirectory}

Notice the link is valid for a limited period of time.

$warnings
EOF;

		print $content;
	}
}

?>
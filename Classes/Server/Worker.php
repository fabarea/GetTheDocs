<?php

// @todo
#$this->generateConf();
#$this->generateMake();
#$this->build();

class Worker {

	/**
	 * @var string
	 */
	protected $uploadDirectory = '';

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

		// Get user workspace
		$username = !empty($_POST['username']) ? $_POST['username'] : '';

		#$identifier = str_shuffle(uniqid(TRUE)); // not used for now... possible random number

		// Computes property
		$this->userDirectory = "$uploadDirectory/$username";
		$this->uploadDirectory = "$uploadDirectory/$username/$fileName";
		$this->buildDirectory = "$filesDirectory/$username/$fileName";
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
		$this->cleanUp();
		$this->displayFeedback();
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	public function renderDocs() {
		// Temp code
		$content = 'Work in progres...';
		file_put_contents("$this->buildDirectory/index.html", $content);
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
		$zip = new ZipArchive();
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
		rrmdir($this->userDirectory);
	}

	/**
	 * Unzip archive
	 *
	 * @return void
	 */
	public function displayFeedback() {
		$content = <<< EOF

Documentation has been gerenated succesfully!

Read documentation on-line:
{$this->url}{$this->buildDirectory}

Download HTML version:
@todo

Download PDF:
@todo

Notice the link is valid for a limited period of time (not defined yet how many days we keep the docs!).
EOF;

		print $content;
	}
}

# recursively remove a directory
function rrmdir($dir) {
	foreach (glob($dir . '/*') as $file) {
		if (is_dir($file))
			rrmdir($file);
		else
			unlink($file);
	}
	rmdir($dir);
}


?>
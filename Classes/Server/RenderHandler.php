<?php

// @todo
#$this->generateConf();
#$this->generateMake();
#$this->build();

class RenderHandler {

	/**
	 * @var string
	 */
	protected $source = '';

	/**
	 * @var string
	 */
	protected $uploadDirectory = '';

	/**
	 * @var string
	 */
	protected $docsDirectory = '';

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

		$this->check();
		$uploadDirectory = 'upload';
		$filesDirectory = 'files';
		$this->settings['archive'] = $_FILES['archive']['name'];
		$username = !empty($_POST['username']) ? $_POST['username'] : '';

		$this->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		#$identifier = str_shuffle(uniqid(TRUE));
		$fileInfo = pathinfo($this->settings['archive']);
		$fileName = $fileInfo['filename'];
		$this->uploadDirectory = $uploadDirectory . '/' . $username . '/';
		$this->source = $this->uploadDirectory . '/' . $fileName;
		$this->docsDirectory = $filesDirectory . '/' . $username . '/';
	}

	/**
	 * Process the User request
	 *
	 * @return void
	 */
	public function process() {
		$this->prepare();
		#$this->unPack();
		$this->cleanUp();
		$this->feedback();
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

		if (!is_dir($this->uploadDirectory)) {
			$result = mkdir($this->uploadDirectory, 0755, TRUE);

			if ($result === FALSE) {
				throw new Exception('Exception: directory not created on the server "' . $this->uploadDirectory . '"');
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
		rrmdir($this->uploadDirectory);
	}

	/**
	 * Unzip archive
	 *
	 * @return void
	 */
	public function feedback() {

		$directory = $this->docsDirectory;
		$url = $this->url;
		$content = <<< EOF

Documentation has been gerenated succesfully!

Read documentation on-line:
$url$directory

Download HTML:
$this->url{$this->uploadDirectory}

Download PDF:

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
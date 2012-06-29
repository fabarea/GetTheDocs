<?php

class ServerConvert {

	/**
	 * @var string
	 */
	protected $uploadDirectory = '';

	/**
	 * @var string
	 */
	protected $uploadDirectoryShortPath = '';

	/**
	 * @var string
	 */
	protected $zipFile = '';

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
	protected $docWorkspace = 'manual';

	/**
	 * @var string
	 */
	protected $homeDirectory = '';

	/**
	 * @var string
	 */
	protected $url = '';

	/**
	 * @var string
	 */
	protected $manualFile = '';

	/**
	 * @var array
	 */
	protected $file = array();

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
		if (!empty($files['manual'])) {
			$this->file = $files['manual'];
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
		$this->handleUpload();
		$this->render();
		$this->displayFeedback();
		$this->cleanUp();
	}

	/**
	 * Check values are correct
	 *
	 * @throws Exception
	 * @return void
	 */
	 protected  function check() {
		if (empty($this->file) || $this->file['error'] != 0) {
			throw new Exception('missing file manual');
		}
		if ($this->parameters['user_workspace'] == '') {
			throw new Exception('missing user_workspace parameter');
		}
	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	 protected  function initialize() {

		 $this->userWorkspace = $this->parameters['user_workspace'];
		 $this->userWorkspacePath = FILES_DIRECTORY . "/$this->userWorkspace";
		 $this->zipFile = "$this->userWorkspacePath/Documentation.zip";

		 $this->homeDirectory = dirname($_SERVER['SCRIPT_FILENAME']);
		 $this->uploadDirectory = "$this->homeDirectory/" . UPLOAD_DIRECTORY . "/$this->userWorkspace/$this->docWorkspace";
		 $this->uploadDirectoryShortPath = $this->uploadDirectory;
		 $this->uploadDirectory .= "/Documentation/_not_versioned/_genesis"; // the conversion script is coded to have this path

		 $this->manualFile = "$this->uploadDirectory/manual.sxw";
		 $this->url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['PHP_SELF']);
	}

	/**
	 * Check that the value is correct
	 *
	 * @return void
	 */
	protected function render() {
		$toolsHome = "/home/render/Resources/Private/RestTools/RenderOfficialDocsFirsttime";

		// Old (manual) commands
		#$commands[] = "cd $this->uploadDirectory; python $toolsHome/documentconverter.py manual.sxw manual.html";
		#$commands[] = "cd $this->uploadDirectory; python $toolsHome/copyclean.py manual.html manual-cleaned.html";
		#$commands[] = "cd $this->uploadDirectory; tidy -asxhtml -utf8 -f errorfile.txt -o manual-tidy.html manual-cleaned.html";
		#$commands[] = "cd $this->uploadDirectory; python $toolsHome/ooxhtml2rst.py manual-tidy.html manual.rst";
		#$commands[] = "cd $this->uploadDirectory; python $toolsHome/normalize_empty_lines.py  manual.rst  temp.rst";
		#$commands[] = "cd $this->uploadDirectory; cp temp.rst manual.rst";

		$commands = array();
		$commands[] = "cd $this->uploadDirectoryShortPath; python $toolsHome/1_do_the_work.py Documentation/_not_versioned/_genesis/manual.sxw > /dev/null";
		$commands[] = "cd $this->uploadDirectoryShortPath; rm -rf Documentation/_*";
		$commands[] = "cd $this->uploadDirectoryShortPath/; zip -qr Documentation.zip Documentation";
		$commands[] = "mv $this->uploadDirectoryShortPath/Documentation.zip $this->zipFile";

		Command::execute($commands);
	}

	/**
	 * Prepare environment
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function prepare() {
		$directories = array($this->uploadDirectory, $this->userWorkspacePath);
		foreach ($directories as $directory) {
			if (!is_dir($directory)) {
				$result = mkdir($directory, 0775, TRUE);

				if ($result === FALSE) {
					throw new Exception('Exception: directory not created on the server "' . $directory . '"');
				}
			}
		}
	}

	/**
	 * Move uploaded file to the right directory
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function handleUpload() {
		// remove file first if exists
		if ($this->manualFile) {
			unlink($this->manualFile);
		}

		// Move file
		$result = move_uploaded_file($this->file['tmp_name'], "$this->manualFile");
		if (!$result) {
			throw new Exception('File not uploaded correctly');
		}
	}

	/**
	 * Clean up environment
	 *
	 * @return void
	 */
	protected function cleanUp() {
		// Remove directory
		File::removeDirectory($this->uploadDirectoryShortPath);
	}

	/**
	 * Unzip archive
	 *
	 * @return void
	 */
	protected function displayFeedback() {

		$content = <<< EOF

manual.sxw has been converted to reST and is available for download.

$this->url$this->zipFile

The automatic conversion is not perfect though and some manual work might be needed.

Notice, generated files are automatically removed after a grace period!

EOF;

		Output::write($content);
	}
}

?>
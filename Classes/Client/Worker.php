<?php
/*                                                                        *
 * This script handles documentation for the TYPO3 project                *
 * @todo: add repository link here
 * @todo: add license + ...
 *                                                                        */

// @todo code an HTML version for http://preview.docs.typo3.org/getthedocs
// @todo add some conversation with the User to generate a file containing the information below
define('USERNAME', 'john');
define('HOST', 'preview.docs.typo3.org/getthedocs');
#define('HOST', 'getthedocs.typo3.fab');

try {
	$worker = new Worker();
	$worker->dispatch($argv);
}
catch (Exception $e) {
	print $e;
}

/**
 * Worker class to make the job done!
 */
class Worker {

	/**
	 * @var array
	 */
	protected $dataSet = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->dataSet['render'] = 'RenderHandler';
		$this->dataSet['fetch'] = 'FetchHandler';
		$this->dataSet['convert'] = 'ConvertHandler';
	}

	/**
	 * Dispatch the job
	 *
	 * @param array $arguments
	 * @return void
	 */
	public function dispatch($arguments) {

		if (count($arguments) <= 1 || $arguments[1] == 'help') {
			$this->displayUsage();
		}
		else {
			$action = $arguments[1];
			if (!empty($this->dataSet[$action])) {
				$className = $this->dataSet[$action];
				array_shift($arguments); // we don't need that one
				$worker = new $className($arguments);
				$worker->work();
			}
			else {
				$message = <<< EOF
I don't know action: "$action". Mistyping?

"get-the-docs help" for information.
EOF;
				Console::output($message);
				die();
			}
		}
	}

	/**
	 * Output a usage message on the console
	 *
	 * @return void
	 */
	public function displayUsage() {
		$usage = <<< EOF
Toolbox for managing TYPO3 documentation

Usage:
	get-the-docs render   Render documentation on-line
	get-the-docs fetch    Download files related to documentation
	get-the-docs convert  Convert legacy OpenOffice documentation to reST
EOF;
		print $usage;
		die();
	}

	/**
	 * Output debug message on the console.
	 *
	 * @return void
	 */
	public function output($message = '') {
		if (is_array($message) || is_object($message)) {
			print_r($message);
		} elseif (is_bool($message)) {
			var_dump($message);
		} else {
			print $message . chr(10);
		}
	}
}

function __autoload($className) {
	if (!class_exists($className)) {
		include  $className . '.php';
	}
}

?>
<?php
/**
 * Worker class to make the job done!
 */
class Dispatcher {

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
				$class = new $className($arguments);
				$class->work();
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
	get-the-docs render     Render documentation remotely
	get-the-docs convert    Convert legacy OpenOffice documentation to reST (Not yet implemented)
EOF;
		print $usage;
		die();
	}
}

?>
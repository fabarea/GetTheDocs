<?php
/**
 * Worker class to make the job done!
 */
class Client {

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
			$className = ucfirst($action) . 'Handler';
			if (class_exists($className)) {
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
	get-the-docs convert    Convert legacy OpenOffice documentation to reST
	get-the-docs config     Download configuration for local rendering (Working but still missing features...)
	get-the-docs help       Print this help
EOF;
		print $usage;
		die();
	}
}

?>
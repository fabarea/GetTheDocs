<?php
/**
 * Console helper
 */
class Command {

	/**
	 * @var boolean
	 */
	static public $dryRun = FALSE;

	/**
	 * @var boolean
	 */
	static public $verbose = FALSE;

	/**
	 * Execute shell commands
	 *
	 * @param mixed $commands
	 * @return array
	 */
	static public function execute($commands) {

		$result = array();
			// dryRun will output the message
			if (is_string($commands)) {
				$commands = array($commands);
			}

			foreach ($commands as $command) {
				if (self::$dryRun) {
					self::output($command);
				} else {
					system($command, $result);
				}
			}
		return $result;
	}

	/**
	 * Output debug message on the console.
	 *
	 * @return void
	 */
	static public function output($message = '') {
		if (is_array($message) || is_object($message)) {
			print_r($message);
		} elseif (is_bool($message)) {
			var_dump($message);
		} else {
			print $message . chr(10);
		}
	}
}

?>
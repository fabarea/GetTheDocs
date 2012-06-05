<?php
/**
 * Console helper
 */
class Console {

	/**
	 * @var boolean
	 */
	static public $dryRun = FALSE;

	/**
	 * @var boolean
	 */
	static public $verbose = FALSE;

	/**
	 * PARSE ARGUMENTS
	 *
	 *  php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --alsofunny=spam=eggs \
	 * > 'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
	 * $out = array(12) {
	 *   [0]                => string(9) "plain-arg"
	 *   ["foo"]            => bool(true)
	 *   ["bar"]            => string(3) "baz"
	 *   ["funny"]          => string(9) "spam=eggs"
	 *   ["alsofunny"]      => string(9) "spam=eggs"
	 *   [1]                => string(11) "plain arg 2"
	 *   ["a"]              => bool(true)
	 *   ["b"]              => bool(true)
	 *   ["c"]              => bool(true)
	 *   ["k"]              => string(5) "value"
	 *   [2]                => string(11) "plain arg 3"
	 *   ["s"]              => string(9) "overwrite"
	 * }
	 *
	 * @author              Patrick Fisher <patrick@pwfisher.com>
	 * @since               August 21, 2009
	 * @see                 http://www.php.net/manual/en/features.commandline.php
	 *                      #81042 function arguments($argv) by technorati at gmail dot com, 12-Feb-2008
	 *                      #78651 function getArgs($args) by B Crawford, 22-Oct-2007
	 * @usage               $args = Console::parseArgs($_SERVER['argv']);
	 */
	public static function parseArgs($argv) {

		array_shift($argv);
		$out = array();

		foreach ($argv as $arg) {

			// --foo --bar=baz
			if (substr($arg, 0, 2) == '--') {
				$eqPos = strpos($arg, '=');

				// --foo
				if ($eqPos === false) {
					$key = substr($arg, 2);
					$value = isset($out[$key]) ? $out[$key] : true;
					$out[$key] = $value;
				}
				// --bar=baz
				else {
					$key = substr($arg, 2, $eqPos - 2);
					$value = substr($arg, $eqPos + 1);
					$out[$key] = $value;
				}
			}
			// -k=value -abc
			else if (substr($arg, 0, 1) == '-') {

				// -k=value
				if (substr($arg, 2, 1) == '=') {
					$key = substr($arg, 1, 1);
					$value = substr($arg, 3);
					$out[$key] = $value;
				}
				// -abc
				else {
					$chars = str_split(substr($arg, 1));
					foreach ($chars as $char) {
						$key = $char;
						$value = isset($out[$key]) ? $out[$key] : true;
						$out[$key] = $value;
					}
				}
			}
			// plain-arg
			else {
				$value = $arg;
				$out[] = $value;
			}
		}
		return $out;
	}

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
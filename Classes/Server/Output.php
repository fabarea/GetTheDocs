<?php
/**
 * Class to output content
 */
final class Output {

	/**
	 * The format to be output. Possibles values: text, html
	 *
	 * @var string
	 */
	static public $format = 'text';


	/**
	 * Output message.
	 *
	 * @return void
	 */
	static public function write($message = '') {
		if (is_array($message) || is_object($message)) {
			print_r($message);
		} elseif (is_bool($message)) {
			var_dump($message);
		} else {
			// parse links
			if (self::$format == 'html') {
				$message = preg_replace('/(http:\/\/[^\n ]+)/is', '<a href="$1" target="_blank">$1</a>', $message);
			}
			print $message . PHP_EOL;
		}
	}
}

?>
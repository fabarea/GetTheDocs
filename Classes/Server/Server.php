<?php
/**
 * Worker class to make the job done!
 */
class Server {

	/**
	 * The format to be output. Possibles values: text, html
	 *
	 * @var string
	 */
	static public $format = 'text';

	/**
	 * Dispatch the job
	 *
	 * @return void
	 */
	public function dispatch() {

		// Handle GET / POST
		$parameters = array_merge($_POST, $_GET);
		$parameters = array_map('trim', $parameters);
		$files = $_FILES;

		// Add possible debug flag
		if (isset($parameters['debug']) && $parameters['debug'] == 1) {
			Command::$dryRun = TRUE;
		}

		// Call the right handler
		switch ($parameters['action']) {
			case 'render':
				$agent = new ServerRender($parameters, $files);
				break;
			case 'config':
				$agent = new ServerConfig($parameters);
				break;
			case 'convert':
				$agent = new ServerAgent($parameters, $files);
				break;
			default:
				$message = <<< EOF
I don't know action: "$action". API problem?
EOF;
				print $message;
				die();
		}

		$agent->process();
	}

	/**
	 * Output message.
	 *
	 * @return void
	 */
	static public function output($message = '') {
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
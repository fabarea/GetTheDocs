<?php
/**
 * Worker class to make the job done!
 */
class Server {

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
				$agent = new RenderAgent();
				break;
			case 'config':
				$agent = new ConfigAgent();
				break;
			case 'convert':
				$agent = new ConvertAgent();
				break;
			default:
				$message = <<< EOF
I don't know action: "$action". API problem?
EOF;
				print $message;
				die();
		}

		$agent->check($parameters, $files);
		$agent->initialize($parameters, $files);
		$agent->process();
	}
}

?>
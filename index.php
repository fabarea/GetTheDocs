<?php

if (strpos($_SERVER['HTTP_USER_AGENT'], 'curl') !== FALSE) {
	try {
		// Add possible debug flag
		if (isset($_POST['debug']) && $_POST['debug'] == 1) {
			Command::$dryRun = TRUE;
		}

		// Call the right handler
		$action = $_POST['action'];
		switch($action) {
			case 'render':
				$agent = new RenderAgent();
				break;
			default:
				$message = <<< EOF
I don't know action: "$action". API problem?
EOF;
				print $message;
				die();
		}
		$agent->work();
	}
	catch (Exception $e) {
		print $e;
	}
}
else {
	$message = 'Browser not supported yet!';
	echo $message;
}

function __autoload($className) {
	$path = 'Classes/Server/' . $className . '.php';
	if (!class_exists($className) && file_exists($path)) {
		include($path);
	}
}
?>
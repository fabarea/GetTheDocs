<?php

require_once 'Classes/Util/Console.php';
require_once 'Classes/Util/File.php';
require_once 'Classes/Util/Template.php';

if (strpos($_SERVER['HTTP_USER_AGENT'], 'curl') !== FALSE) {
	try {
		// Add possible debug flag
		if (isset($_POST['debug']) && $_POST['debug'] == 1) {
			Console::$dryRun = TRUE;
		}

		// Call the right handler
		$action = $_POST['action'];
		switch($action) {
			case 'render':
				$worker = new Server\RenderHandler();
				break;
			default:
				$message = <<< EOF
I don't know action: "$action". API problem?
EOF;
				print $message;
				die();
		}
		$worker->work();
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
	$classParts = explode("\\", $className);
	$path = 'Classes/' . implode('/', $classParts) . '.php';
	if (!class_exists($className) && file_exists($path)) {
		include($path);
	}
}
?>
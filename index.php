<?php

// Configuration
define('UPLOAD', 'upload');
define('FILES', 'files');

// Reply only to certain HTTP AGENT
if (strpos($_SERVER['HTTP_USER_AGENT'], 'curl') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'PHP/') !== FALSE) {
	try {
		$server = new Server();
		$server->dispatch();
	}
	catch (Exception $e) {
		print $e;
	}
}
else {
	print file_get_contents('usage.html');
}

/**
 * Auto load handling
 *
 * @param string $className
 */
function __autoload($className) {
	$path = 'Classes/Server/' . $className . '.php';
	if (!class_exists($className) && file_exists($path)) {
		include($path);
	}
}
?>
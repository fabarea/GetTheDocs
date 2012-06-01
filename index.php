<?php

if (strpos($_SERVER['HTTP_USER_AGENT'], 'curl') !== FALSE) {
	try {
		$worker = new Worker();
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
	if (!class_exists($className)) {
		include  'Classes/Server/' . $className . '.php';
	}
}
?>
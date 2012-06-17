<?php

// Configuration
define('UPLOAD', 'upload');
define('FILES', 'files');
define('API_VERSION', '1.0.0');

$process = FALSE;
// case 1: the request comes from the CLI client
// case 2: the request comes from the Web form
if (strpos($_SERVER['HTTP_USER_AGENT'], 'PHP/') !== FALSE) {

	$clientApiVersion = $_POST['api_version'];
	$serverApiVersion = API_VERSION;
	if ($clientApiVersion != $serverApiVersion) {
		$content = <<<EOF

Hang on! It looks your client is out of date with version "$clientApiVersion"

Please update your script to version "$serverApiVersion".

curl -s http://preview.docs.typo3.org/getthedocs/get-the-docs.php > get-the-docs
EOF;
		print $content;
		die();
	}

	$process = TRUE;
	Server::$format = 'text';
} elseif (!empty($_FILES['zip_file'])){

	// web form case
	$formats = array();
	if (!empty($_POST['html'])) {
		$formats[] = 'html';
		unset($_POST['html']);
	}

	if (!empty($_POST['pdf'])) {
		$formats[] = 'pdf';
		unset($_POST['pdf']);
	}

	if (!empty($_POST['epub'])) {
		$formats[] = 'epub';
		unset($_POST['epub']);
	}

	// Render HTML if anything defined (which is mistake coming from the user)
	if (empty($formats)) {
		$formats[] = 'html';
	}

	if (empty($_POST['doc_name'])) {
		$_POST['doc_name'] = 'undefined';
	}

	if (empty($_POST['doc_workspace'])) {
		$_POST['doc_workspace'] = 'undefined';
	}

	$_POST['action'] = 'render';
	$_POST['user_workspace'] = 'web';
	$_POST['format'] = implode(',', $formats);
	$_POST['debug'] = 0;

	$process = TRUE;
	Server::$format = 'html';
}

if ($process) {
	try {
		$server = new Server();
		$server->dispatch();
	} catch (Exception $e) {
		print $e;
	}
} else {
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
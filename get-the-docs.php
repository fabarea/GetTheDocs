#!/usr/bin/env php
<?php

$files = array('Worker', 'Console', 'FetchHandler', 'RenderHandler', 'ConvertHandler');
$directoryHome = 'Classes/Client';

$content = '';
foreach ($files as $file) {
	$filePath = $directoryHome . '/' . $file . '.php';
	if (is_file($filePath)) {
		$content .= file_get_contents($directoryHome . '/' . $file . '.php');
	}
}

print $content;
?>
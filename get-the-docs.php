#!/usr/bin/env php
<?php echo "<?php" ?>

/*                                                                        *
 * This script handles documentation for the TYPO3 project                *
 * @todo: add repository link here
 * @todo: add license + ...
 *                                                                        */

// @todo code an HTML version for http://preview.docs.typo3.org/getthedocs
// @todo add some conversation with the User to generate a file containing the information below
define('USERNAME', 'anonymous');
define('HOST', 'preview.docs.typo3.org/getthedocs');
define('CURL', '/usr/bin/curl');

try {
	$dispatcher = new Dispatcher();
	$dispatcher->dispatch($argv);
}
catch (Exception $e) {
	print $e;
}

<?php echo "?>" ?>
<?php
$files = array('Client/Dispatcher', 'Client/Console', 'Client/FetchHandler', 'Client/RenderHandler', 'Client/ConvertHandler');
$directoryHome = 'Classes';

$content = '';
foreach ($files as $file) {
	$filePath = $directoryHome . '/' . $file . '.php';
	if (is_file($filePath)) {
		$content .= file_get_contents($directoryHome . '/' . $file . '.php');
	}
}

print $content;
?>
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
define('HOST', 'http://preview.docs.typo3.org/getthedocs/');
define('CURL', '/usr/bin/curl');

try {
	$client = new Client();
	$client->dispatch($argv);
}
catch (Exception $e) {
	print $e;
}

<?php echo "?>" ?>
<?php
// Fetch content from files
$content = '';
foreach (glob('Classes/Client/*') as $file) {
	$content .= file_get_contents($file);
}

print $content;
?>
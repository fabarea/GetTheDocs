#!/usr/bin/env php
<?php echo "<?php" ?>

/*                                                                        *
 * This script handles documentation for the TYPO3 project                *
 * @todo: add repository link here
 * @todo: add license + ...
 *                                                                        */

if (! class_exists('ZipArchive')) {
	$message = "\nMissing PHP ZipArchive Class. Try to install \"php5-zip\" package.\n";
	die($message);
}

$userWorkspace = 'anonymous';
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && exec('whoami') !== '') {
	$userWorkspace = exec('whoami');
}

define('USER_WORKSPACE', $userWorkspace);
define('HOST', 'http://preview.docs.typo3.org/getthedocs/');
define('API_VERSION', '1.0.0');

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
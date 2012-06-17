#!/usr/bin/env php
<?php echo "<?php" ?>

/*                                                                        *
 * This script handles documentation for the TYPO3 project                *
 * @todo: add repository link here
 * @todo: add license + ...
 *                                                                        */

// @todo code an HTML version for http://preview.docs.typo3.org/getthedocs
// @todo add some conversation with the User to generate a file containing the information below

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
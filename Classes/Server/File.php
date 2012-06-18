<?php

class File {

	/**
	 * Recursively remove directory
	 *
	 * @static
	 * @param $dir
	 * @return void
	 */
	static public function removeDirectory($dir) {

		$command = "rm -rf $dir";
		if (is_dir($dir)) {
			exec($command);
		}
		#foreach (glob($dir . '/*') as $file) {
		#	if (is_dir($file))
		#		self::removeDirectory($file);
		#	else
		#		$result = unlink($file);
		#		if (! $result) {
		#			throw new Exception("problem deleting file $file");
		#		}
		#}
		#rmdir($dir);
	}
}


?>
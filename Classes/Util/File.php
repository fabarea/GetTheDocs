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
		foreach (glob($dir . '/*') as $file) {
			if (is_dir($file))
				self::removeDirectory($file);
			else
				unlink($file);
		}
		rmdir($dir);
	}
}


?>
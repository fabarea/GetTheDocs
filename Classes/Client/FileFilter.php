<?php

/**
 * Filter File according to a regex
 */
class FileFilter extends AbstractRegexFilter {

	/**
	 * Filter files against the regex
	 *
	 * @return bool
	 */
	public function accept() {
		return (!$this->isFile() || preg_match($this->regex, $this->getFilename()));
	}
}

?>
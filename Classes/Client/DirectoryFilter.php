<?php

/**
 * Filter Directory according to a regex
 */
class DirectoryFilter extends AbstractRegexFilter {

	/**
	 * Filter directories against the regex
	 *
	 * @return bool
	 */
	public function accept() {
		return (!$this->isDir() || preg_match($this->regex, $this->getFilename()));
	}
}

?>
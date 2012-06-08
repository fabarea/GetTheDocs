<?php

/**
 * Abstract Class for filtering file resource
 */
abstract class AbstractRegexFilter extends RecursiveRegexIterator {

	/**
	 * @var string
	 */
	protected $regex;

	/**
	 * Constructor
	 *
	 * @param RecursiveIterator $iterator
	 * @param string $regex
	 */
	public function __construct(RecursiveIterator $iterator, $regex) {
		$this->regex = $regex;
		parent::__construct($iterator, $regex);
	}
}
?>
<?php

/**
 * PHP version: 5.6.24
 */

/**
 * Thrown when a Chain of Responsability ends without being able to handle the task.
 */
class EndOfChainException extends Exception {
	public function __construct() {
		parent::__construct( "The chain was unable to handle a task.\nContents of GET:\n" . implode( ' ', $_GET ) );
	}
}


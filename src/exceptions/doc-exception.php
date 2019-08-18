<?php

/**
 * PHP version: 5.6.24
 */

/**
 * Thrown when a problem occurs when handling HTML documents.
 */
class DocException extends Exception {
	public function __construct( $doc_path = '' ) {
		parent::__construct( "Handling of HTML document $doc_path failed!" );
	}
}

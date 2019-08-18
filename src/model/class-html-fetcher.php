<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/../config.php';
require_once PATH_EXCEPTIONS . 'doc-exception.php';

/*
 * Disable standard libxml errors and enable user error handling.
 * This is used to suppresss DOMDocument errors/warrnings on HTML5 tags.
 */
libxml_use_internal_errors( true );

/**
 * Fetch HTML files from disk.
 */
class HTML_Fetcher {
	/**
	 * Fetch an html file.
	 *
	 * @param $doc_name name of the document.
	 *
	 * @return string representation of the document.
	 *
	 * @throws InvalidArgumentException if argument is not a string.
	 * @throws DocException if an error occurs when loading the document.
	 */
	public function fetch_html_doc( $doc_name ) {
		if ( ! is_string( $doc_name ) ) {
			throw new InvalidArgumentException();
		}
		$sanitized_doc_name = $this->sanitize( $doc_name );
		$html = file_get_contents( PATH_HTML . $sanitized_doc_name );
		if ( false === $html ) {
			throw new DocException( $sanitized_doc_name );
		}
		return $html;
	}

	/**
	 * Fetch an html file.
	 *
	 * @param $doc_name name of the document.
	 *
	 * @return DOMDocument representation of the document.
	 *
	 * @throws InvalidArgumentExcepiton if argument is not a string.
	 * @throws DocException if an error occurs when loading the document.
	 */
	public function fetch_dom_doc( $doc_name ) {
		if ( ! is_string( $doc_name ) ) {
			throw new InvalidArgumentException();
		}
		$doc = new DOMDocument();
		$sanitized_doc_name = $this->sanitize( $doc_name );
		$status = $doc->loadHTMLFile( PATH_HTML . $sanitized_doc_name,
			LIBXML_COMPACT /* Optimization. */
			| LIBXML_HTML_NODEFDTD /* Prevent a default doctype being added when one is not found. */
		);
		if ( false === $status ) {
			throw new DocException( $sanitized_doc_name );
		}
		return $doc;
	}

	/**
	 * Sanitize the name of a document.
	 *
	 * @param $doc_name name of the document.
	 *
	 * @return string sanitized document name.
	 *
	 * @throws InvalidArgumentException if the document name is invalid.
	 */
	private function sanitize( $doc_name ) {
		if ( ! is_string( $doc_name ) ) {
			throw new InvalidArgumentException();
		}
		/* Max characters. */
		$length = strlen( $doc_name );
		if ( $length >= 128 ) {
			throw new InvalidArgumentException();
		}
		/* Whitelist: a-z (case-insensitive), 0-9, - and . are allowed. */
		for ( $index = 0; $index < $length; ++$index ) {
			$chr = substr( $doc_name, $index, 1 );
			if ( 0 === preg_match( '/^([a-z]|[0-9]|-|\.)$/i', $chr ) ) {
				throw new InvalidArgumentException();
			}
		}
		return $doc_name;
	}

	/**
	 * @param $doc DOM representation of the HTML.
	 *
	 * @return string DOM representation to string.
	 *
	 * @throws DocException if the HTML is invalid.
	 */
	private function html_to_string( DOMDocument $doc ) {
		$html = $doc->saveHTML();
		if ( false === $html ) {
			throw new DocException();
		}
		return $html;
	}
}

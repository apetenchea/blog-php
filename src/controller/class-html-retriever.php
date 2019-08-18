<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/../config.php';
require_once PATH_CONTROLLER . 'class-chain.php';
require_once PATH_MODEL . 'class-model.php';

/**
 * Retrive an HTML file from disk.
 */
class HTML_Retriever extends Chain {
	public function handle( Model $model ) {
		if ( isset( $_GET['action'] ) && 'get-page' === $_GET['action'] && isset( $_GET['page'] ) ) {
			return $model->get_doc( $_GET['page'] );
		}
		return $this->pass_onto_next( $model );
	}
}

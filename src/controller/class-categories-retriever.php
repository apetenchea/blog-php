<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/../config.php';
require_once PATH_CONTROLLER . 'class-chain.php';
require_once PATH_MODEL . 'class-model.php';

/**
 * Retrive categories.
 */
class Categories_Retriever extends Chain {
	public function handle( Model $model ) {
		if ( isset( $_GET['action'] ) && 'get-categories' === $_GET['action'] ) {
			return $model->get_all_categories();
		}
		return $this->pass_onto_next( $model );
	}
}


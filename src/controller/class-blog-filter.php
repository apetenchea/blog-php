<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/../config.php';
require_once PATH_CONTROLLER . 'class-chain.php';
require_once PATH_MODEL . 'class-model.php';

/**
 * Returns the blog entries, described by title and introduction.
 * Filters could also be applied here.
 */
class Blog_Filter extends Chain {
	const ENTRIES_PER_PAGE = 8;

	public function handle( Model $model ) {
		if ( isset( $_GET['action'] ) && 'get-blog-entries' === $_GET['action'] ) {
			$page_number = 1;
			if ( isset( $_GET['page-number'] ) && is_numeric( $_GET['page-number'] ) ) {
				$page_number = intval( $_GET['page-number'] );
			}
			if ( ! isset( $_GET['filter'] ) || empty( $_GET['filter'] ) ) {
				$articles = $model->get_all_articles( $page_number );
			} else {
				$articles = $model->filter_articles( $_GET['filter'], $page_number );
			}
			$result = array();
			foreach ( $articles as $index => $article ) {
				$result[ $index ] = $model->get_article_overview( $article['name'] );
				$result[ $index ]['categories'] = $model->get_article_categories( $article['name'] );
			}
			return $result;
		}
		return $this->pass_onto_next( $model );
	}
}

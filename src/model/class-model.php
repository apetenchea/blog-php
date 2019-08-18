<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/../config.php';
require_once PATH_MODEL . 'class-html-fetcher.php';
require_once PATH_MODEL . 'class-db.php';

/**
 * Handle application data.
 */
class Model {
	/**
	 * @param $doc_name name of the document.
	 *
	 * @return string html.
	 *
	 * @throws InvalidArgumentException if argument is not a string.
	 * @throws DocException if the document cannot be retrived.
	 */
	public function get_doc( $doc_name ) {
		if ( ! is_string( $doc_name ) ) {
			throw new InvalidArgumentException();
		}
		if ( strlen( $doc_name ) > 100 ) {
			throw new InvalidArgumentException();
		}
		$doc_fetcher = new HTML_Fetcher();
		return $doc_fetcher->fetch_html_doc( $doc_name . '.html' );
	}

	/**
	 * @param $article_name string name of the article.
	 *
	 * @return an object of the form {string: title, string: intro}.
	 *
	 * @throws InvalidArgumentException if argument is not a string.
	 * @throws DocException if an error occurs when retriving the article.
	 */
	public function get_article_overview( $article_name ) {
		if ( ! is_string( $article_name ) ) {
			throw new InvalidArgumentException();
		}
		$doc_fetcher = new HTML_Fetcher();
		$dom_doc = $doc_fetcher->fetch_dom_doc( $article_name . '.html' );
		$article_title = $dom_doc->getElementById( 'title' );
		if ( null === $article_title ) {
			throw new DocException( $article_name );
		}
		$result = array();
		$result['name'] = $article_name;
		$result['title'] = $article_title->textContent;
		$article_intro = $dom_doc->getElementById( 'intro' );
		if ( null === $article_intro ) {
			throw new DocException( $articles );
		}
		$result['intro'] = $article_intro->textContent;
		return $result;
	}

	/**
	 * @return an associative array containing all the categories, in the form (name, color).
	 *
	 * @throws PDOException if an error occurs involving the database.
	 */
	public function get_all_categories() {
		$db = Db::get_instance();
		return $db->get_categories( null );
	}

	/**
	 * @param string name of the article.
	 *
	 * @return an array containing all the categories to which the article belongs.
	 *
	 * @throws InvalidArgumentException if an argument is invalid.
	 * @throws PDOException if a an error occurs involving the database.
	 */
	public function get_article_categories( $article_name ) {
		if ( ! is_string( $article_name ) ) {
			throw InvalidArgumentException();
		}
		$db = Db::get_instance();
		return $db->get_categories( $article_name );
	}

	/**
	 * @param int page_number number of the requested page.
	 *
	 * @return an array containing all the articles.
	 *
	 * @throws InvalidArgumentException if an argument is invalid.
	 * @throws PDOException if an error occurs involving the database.
	 */
	public function get_all_articles( $page_number = 1 ) {
		if ( ! is_numeric( $page_number ) || $page_number < 1 || $page_number > 10000 ) {
			throw new InvalidArgumentException();
		}
		return $this->filter_articles( null, $page_number );
	}

	/**
	 * @param string categories separated by a comma.
	 * @param int page_number number of the requested page.
	 *
	 * @return an associative array containing all the articles belonging to the given categories.
	 *
	 * @throws InvalidArgumentException if an argument is invalid.
	 * @throws PDOException if an erro occurs involving the database.
	 */
	public function filter_articles( $filter, $page_number = 1 ) {
		if ( ! ( is_string( $filter ) || is_null( $filter ) ) || ! is_numeric( $page_number ) || $page_number < 1 || $page_number > 10000 ) {
			throw InvalidArgumentException();
		}
		$db = Db::get_instance();
		return $db->filter_articles( $filter, ( $page_number - 1 ) * ARTICLES_PER_PAGE, ARTICLES_PER_PAGE );
	}
}

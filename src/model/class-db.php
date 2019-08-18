<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/../config.php';

/**
 * Database.
 */
class Db {
	private static $singleton;
	private $_connection;

	/**
	 * Get the one and only instance.
	 *
	 * @throws PDOException if the attempt to connect to the database fails.
	 */
	public static function get_instance() {
		if ( null === self::$singleton ) {
			self::$singleton = new Db();
		}
		return self::$singleton;
	}

	/**
	 * Create a database connection.
	 *
	 * @throws PDOException if the attempt to connect to the database fails.
	 */
	private function __construct() {
		$this->_connection = new PDO(
			'mysql:host=' . SERVER_NAME . ';'
			. 'dbname=' . DB_NAME,
			DB_USER,
			DB_PASSWORD
		);
		/* Configure the PDO connection to throw exceptions. */
		$this->_connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	}

	/**
	 * Close database connection.
	 */
	public function __destruct() {
		$this->_connection = null;
	}

	/**
	 * @param $article_name string name of the article.
	 *
	 * @return if @article_name is not null, returns an associative array containing
	 * all the categories to which the article belongs, otherwise returns an
	 * associative array containing all the categories. Each category is represented
	 * as (name,color).
	 *
	 * @throws InvalidArgumentException if an argument is invalid.
	 * @throws PDOException if the query cannot be executed.
	 */
	public function get_categories( $article_name ) {
		if ( ! ( is_null( $article_name ) || is_string( $article_name ) ) ) {
			throw new InvalidArgumentException();
		}
		return $this->query( 'CALL getCategories(?)', $article_name );
	}

	/**
	 * @param $filter string categories separated by a comma.
	 * @param $interval_start start form the limit_start'th article.
	 * @param $interval_size select a total of size articles (if available).
	 *
	 * @return if $filter is not null, returns an associative array containing
	 * all the articles which belong the the categories enumerated in the $filter,
	 * otherwise returns an associative array containing all the articles.
	 *
	 * @throws InvalidArgumentException if an argument is invalid.
	 * @throws PDOException if the query cannot be executed.
	 */
	public function filter_articles( $filter, $interval_start, $interval_size ) {
		if ( ! ( is_null( $filter ) || is_string( $filter ) ) || ! is_numeric( $interval_start ) || ! is_numeric( $interval_size ) ) {
			throw new InvalidArgumentException();
		}
		return $this->query( 'CALL getArticles(?,?,?)', $filter, strval( $interval_start ), strval( $interval_size ) );
	}

	/**
	 * Query the database. All parameters are sanitized.
	 *
	 * @param variable number of arguments (>= 1), out of which the first argument
	 * is a string representing the query, and the rest of the arguments are query paramenters.
	 *
	 * @return on success, it returns the response as an associative array.
	 *
	 * @throws PDOException if the query cannot be executed.
	 * @throws InvalidArgumentException if the query or the parameters are invalid.
	 */
	private function query() {
		/* Used for caching queries. */
		static $stmt_cache = array();

		$numargs = func_num_args();
		if ( 0 == $numargs ) {
			throw new InvalidArgumentException();
		}
		$arg_list = func_get_args();
		if ( ! is_string( $arg_list[0] ) ) {
			throw new InvalidArgumentException();
		}
		$query_string = $this->sanitize( $arg_list[0] );
		$hash = md5( $query_string );
		if ( ! isset( $stmt_cache[ $hash ] ) ) {
			$stmt_cache[ $hash ] = $this->_connection->prepare( $query_string );
		}
		$stmt = $stmt_cache[ $hash ];

		for ( $index = 1; $index < $numargs; ++$index ) {
			$success = $stmt->bindParam(
				$index,
				$this->sanitize( $arg_list[ $index ] ),
				PDO::PARAM_STR
			);
			if ( false === $success ) {
				throw new PDOException();
			}
		}

		$success = $stmt->execute();
		if ( false === $success ) {
			throw new PDOException();
		}

		$success = $stmt->setFetchMode( PDO::FETCH_ASSOC );
		if ( false === $success ) {
			throw new PDOException();
		}

		$response = $stmt->fetchAll();
		if ( false === $response ) {
			throw new PDOException();
		}

		$success = $stmt->closeCursor();
		if ( false === $success ) {
			throw new PDOException();
		}

		return $response;
	}

	/**
	 * Sanitize a part of a query.
	 *
	 * @param $query_string string, or null  as part of a query.
	 *
	 * @return on success, it returns the sanizied query string.
	 *
	 * @throws InvalidArgumentException if the string is invalid.
	 */
	private function sanitize( $query_string ) {
		if ( is_null( $query_string ) ) {
			return null;
		}
		if ( ! is_string( $query_string ) ) {
			throw new InvalidArgumentException();
		}
		/* Max charachers. */
		$length = strlen( $query_string );
		if ( $length >= 256 ) {
			throw new InvalidArgumentException();
		}
		/* Whitelist: a-z (case-insensitive), 0-9, - ? (space) and , are allowed. */
		for ( $index = 0; $index < $length; ++$index ) {
			$chr = substr( $query_string, $index, 1 );
			if ( 0 === preg_match( '/^([a-z]|[0-9]|-|,|\?|\(|\)| )$/i', $chr ) ) {
				throw new InvalidArgumentException();
			}
		}
		return $query_string;
	}
}

<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/../config.php';
require_once PATH_MODEL . 'class-model.php';
require_once PATH_EXCEPTIONS . 'end-of-chain-exception.php';

/**
 * Chain of responsibility.
 *
 * Handle a GET request or pass it the the next in chain, if any.
 */
abstract class Chain {
	private $_nxt;

	public function __construct( Chain $nxt = null ) {
		$this->_nxt = $nxt;
	}

	/**
	 * Pass the responsibility to the next handler.
	 *
	 * @param model part of MVC.
	 *
	 * @throws EndOfChainException if the chain has ended.
	 */
	protected function pass_onto_next( Model $model ) {
		if ( is_null( $this->_nxt ) ) {
			throw new EndOfChainException();
		}
		return $this->_nxt->handle( $model );
	}

	/**
	 * Handle the request if possible, or pass it.
	 *
	 * @param model part of MVC.
	 *
	 * @throws Exception if the request could not be handled.
	 */
	public abstract function handle( Model $model );
}

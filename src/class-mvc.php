<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/config.php';
require_once PATH_MODEL . 'class-model.php';
require_once PATH_VIEW . 'class-view.php';
require_once PATH_CONTROLLER . 'class-controller.php';

/**
 * Model-View-Controller.
 */
class Mvc {
	private $_model;
	private $_view;
	private $_controller;

	public function __construct() {
		$this->_model = new Model();
		$this->_view = new View();
		$this->_controller = new Controller( $this->_model, $this->_view );
	}

	/**
	 * Handle an http request.
	 */
	public function handle_request() {
		$this->_controller->handle_request();
	}
}

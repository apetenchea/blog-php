<?php

/**
 * PHP version: 5.6.24
 */

require_once dirname( __FILE__ ) . '/../config.php';
require_once PATH_CONTROLLER . 'class-html-retriever.php';
require_once PATH_CONTROLLER . 'class-blog-filter.php';
require_once PATH_CONTROLLER . 'class-categories-retriever.php';
require_once PATH_MODEL . 'class-model.php';
require_once PATH_VIEW . 'class-view.php';
require_once PATH_EXCEPTIONS . 'end-of-chain-exception.php';

/**
 * Handle interaction between the View and the Model.
 */
class Controller {
	private $_model;
	private $_view;
	private $_chain;

	public function __construct( Model $model, View $view ) {
		$this->_model = $model;
		$this->_view = $view;
		$this->_chain = new Categories_Retriever( new HTML_Retriever( new Blog_Filter() ) );
	}

	/**
	 * Handle a request and display the response.
	 */
	public function handle_request() {
		$response_state = array(
			'ok' => 200,
			'no-content' => 204,
		);

		$response = new stdClass();
		$response->state = $response_state['ok'];

		try {
			$response->body = $this->_chain->handle( $this->_model );
		} catch (Exception $e) {
			$response->state = $response_state['no-content'];
			if ( APP_TYPE == 'DEV' ) {
				$response->body = $e->__toString();
			}
		}
		$this->_view->show( $response );
	}
}

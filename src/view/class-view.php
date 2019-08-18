<?php

/**
 * PHP version: 5.6.24
 */

/**
 * Serve the formatted response.
 */
class View {
	/**
	 * @param $response object to be encoded into JSON.
	 *
	 * @return string JSON encoding of $json_obj.
	 *
	 * @throws InvalidArgumentException if argument is {@code null}.
	 */
	public function show( $response ) {
		if ( is_null( $response ) ) {
			throw new InvalidArgumentException();
		}
		echo json_encode( $response );
	}
}

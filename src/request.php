<?php

/**
 * Instantiate the MVC and handle a request.
 *
 * PHP version: 5.6.24
 */

require_once 'config.php';
require_once PATH_SRC . 'class-mvc.php';

$mvc = new Mvc();
$mvc->handle_request();

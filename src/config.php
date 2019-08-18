<?php

/**
 * Configuration file.
 *
 * PHP version: 5.6.24
 */

define( 'APP_NAME', 'cdroot' );
define( 'APP_TYPE', 'PROD' );

define( 'PATH_SRC', dirname( __FILE__ ) . '/' );
define( 'PATH_PUBLIC', dirname( __FILE__ ) . '/../public/' );
define( 'PATH_HTML', PATH_PUBLIC . 'html/' );
define( 'PATH_VIEW', PATH_SRC . 'view/' );
define( 'PATH_MODEL', PATH_SRC . 'model/' );
define( 'PATH_CONTROLLER', PATH_SRC . 'controller/' );
define( 'PATH_EXCEPTIONS', PATH_SRC . 'exceptions/' );

define( 'SERVER_NAME', 'localhost' );
define( 'DB_NAME', 'cdroot' );
define( 'DB_USER', 'cdroot' );
define( 'DB_PASSWORD', '****' );

define( 'ARTICLES_PER_PAGE', 8 );

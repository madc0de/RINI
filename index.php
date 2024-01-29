<?php

/**
 * RINI - an extremely simple naked PHP application
 *
 * @package rini
 * @author Madc0de
 * @link https://github.com/madc0de/rini/
 * @license http://opensource.org/licenses/MIT MIT License
 */

// DIRECTORY_SEPARATOR adds a slash to the end of the path
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
// set a constant that holds the project's "application" folder, like "/var/www/application".
define('APP', ROOT . 'application' . DIRECTORY_SEPARATOR);

// This is the auto-loader for Composer-dependencies (to load tools into your project).
require ROOT . 'vendor/autoload.php';

// load application config (error reporting etc.)
require APP . 'Config/config.php';

require APP . 'Core/Common.php';

// load application class
use Rini\Core\Application;

/*
* ------------------------------------------------------
*  Define a custom error handler so we can log PHP errors
* ------------------------------------------------------
*/
set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

// start the application
$app = new Application();

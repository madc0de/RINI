<?php

/*
 * PHP-DB
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Rini\Core\Db\Throwable;

/**
 * Exception that is thrown when an integrity constraint is being violated
 *
 * Common constraints include 'UNIQUE', 'NOT NULL' and 'FOREIGN KEY'
 *
 * Ambiguous column references constitute violations as well
 */
class IntegrityConstraintViolationException extends Exception {}

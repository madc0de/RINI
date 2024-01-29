<?php

/*
 * PHP-DB
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Rini\Core\Db\Throwable;

/** Exception that is thrown when a transaction cannot be started successfully for some reason */
class BeginTransactionFailureException extends TransactionFailureException {}

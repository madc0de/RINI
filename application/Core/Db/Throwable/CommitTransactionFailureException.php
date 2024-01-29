<?php

/*
 * PHP-DB
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Rini\Core\Db\Throwable;

/** Exception that is thrown when a transaction cannot be committed successfully for some reason */
class CommitTransactionFailureException extends TransactionFailureException {}

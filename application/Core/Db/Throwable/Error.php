<?php

/*
 * PHP-DB
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Rini\Core\Db\Throwable;

/** Base class for all conditions that the application might not recover from and thus should not catch */
class Error extends \Exception {}

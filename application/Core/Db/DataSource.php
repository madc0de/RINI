<?php

/*
 * PHP-DB
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Rini\Core\Db;

/** Description of a data source */
interface DataSource {

	/**
	 * Converts this instance to a DSN
	 *
	 * @return Dsn
	 */
	public function toDsn();

}

<?php

namespace Rini\Core;

use PDOStatement;
use InvalidArgumentException;
use Rini\Core\Db\PdoDataSource;
use Rini\Core\Db\PdoDatabase;

class Model
{

    /**
     * The PDO object.
     *
     * @var \Rini\Core\Db\PdoDatabase
     */
    public $db;

    /**
     * The table.
     *
     * @var string
     */
    public $table;

    /**
     * The PDO statement object.
     *
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * The DSN connection string.
     *
     * @var string
     */
    protected $dsn;

    /**
     * The unique global id.
     *
     * @var integer
     */
    protected $guid = 0;

    /**
     * The returned id for the insert.
     *
     * @var string
     */
    public $returnId = '';

    /**
     * Error Message.
     *
     * @var string|null
     */
    public $error = null;

    /**
     * The array of error information.
     *
     * @var array|null
     */
    public $errorInfo = null;


    /**
     * Whenever model is created, open a database connection.
     */
    function __construct() 
    {
        if ( !defined( 'DB_TYPE' ) ) {
            throw new InvalidArgumentException('DB_TYPE not set in config');
        }

        $dataSource = new PdoDataSource(DB_TYPE);
        $dataSource->setHostname(DB_HOST ?? 'localhost');
        $dataSource->setPort(3306);
        $dataSource->setDatabaseName(DB_NAME ?? null);
        $dataSource->setCharset('utf8mb4');
        $dataSource->setUsername(DB_USER ?? null);
        $dataSource->setPassword(DB_PASS ?? null);

        $this->db = PdoDatabase::fromDataSource($dataSource);
    
    }
}
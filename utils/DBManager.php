<?php
require_once('Constants.php');

class DBManager
{

    private $connection;

    public function __construct()
    {
        $this->openConnection();
    }

    public function openConnection()
    {
        $this->connection = mysqli_connect(
            Constants::DB_SERVER_NAME,
            Constants::DB_USER,
            Constants::DB_PASSWORD,
            Constants::DB_NAME
        );

        $this->verifyConnection();
    }

    public function closeConnection()
    {
        mysqli_close($this->connection);
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function queryExecute(string $sqlQuery): mysqli_result
    {
        return mysqli_query($this->connection, $sqlQuery);
    }

    public function queryPrepare(string $sqlQuery): mysqli_stmt
    {
        $stmt = mysqli_prepare($this->connection, $sqlQuery);

        if (!$stmt) {
            throw new Exception(
                "An error occurred during SQL query prepare: " . mysqli_error($this->connection)
            );
        }

        return $stmt;
    }

    private function verifyConnection()
    {
        if (!$this->connection) {
            throw new Exception(
                "Cannot stablish " . Constants::DB_NAME . " database connection by error: " . mysqli_connect_error()
            );
        }
    }
}

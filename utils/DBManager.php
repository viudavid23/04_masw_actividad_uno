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

    /**
     * Method responsible for prepare a query sql.
     * 
     * @param string $query The sql query that will be executed.
     * @return mysqli_stmt $stmt A statement object prepared.
     */
    public function queryPrepare(string $query): mysqli_stmt
    {
        $stmt = mysqli_prepare($this->connection, $query);
        return $stmt ? $stmt : die("An error has occured during query prepare: " . mysqli_error($this->connection));
    }

    private function verifyConnection()
    {
        if (!$this->connection) {
            die("Cannot stablish " . Constants::DB_NAME . " db connection by error: " . mysqli_connect_error());
        }
    }
}

<?php
require_once('../../utils/DBManager.php');
class DirectorSerie
{
    const TABLE_NAME = 'director_serie';
    const COLUMN_DIRECTOR_ID = 'director_id';
    const COLUMN_SERIE_ID = 'serie_id';
    const COLUMN_STATUS = 'status';

    private $directorId;
    private $serieId;
    private $status;

    public function __construct($directorId = null, $serieId = null, $status = null)
    {
        if ($directorId != null) {
            $this->directorId = $directorId;
        }
        if ($serieId != null) {
            $this->serieId = $serieId;
        }
        if ($status != null) {
            $this->status = $status;
        }
    }


    public function getBySerieId(): mixed
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnQuery(self::COLUMN_SERIE_ID));
        $stmt->bind_param('i', $this->serieId);
        $stmt->execute();
        $resultSet = $stmt->get_result();
        $listData = [];

        foreach ($resultSet as $item) {
            $directorSerie = new DirectorSerie($item[self::COLUMN_DIRECTOR_ID], $item[self::COLUMN_SERIE_ID], $item[self::COLUMN_STATUS]);
            array_push($listData, $directorSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function getByDirectorId(): mixed
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnQuery(self::COLUMN_DIRECTOR_ID));
        $stmt->bind_param('i', $this->directorId);
        $stmt->execute();
        $resultSet = $stmt->get_result();
        $listData = [];

        foreach ($resultSet as $item) {
            $directorSerie = new DirectorSerie($item[self::COLUMN_DIRECTOR_ID], $item[self::COLUMN_SERIE_ID], $item[self::COLUMN_STATUS]);
            array_push($listData, $directorSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function save($directorIdsArray): bool
    {
        $created = false;

        $dbManager = new DBManager();

        try {

            $conn = $dbManager->getConnection();

            mysqli_begin_transaction($conn);

            mysqli_autocommit($conn, false);

            $stmt = $dbManager->queryPrepare($this->insertQuery());

            mysqli_stmt_bind_param($stmt, 'ii', $this->directorId, $this->serieId);

            foreach ($directorIdsArray as $directorItemId) {

                $this->setdirectorId($directorItemId);

                mysqli_stmt_execute($stmt);
            }

            $created = mysqli_commit($conn);
        } catch (Exception $e) {
            error_log("Error durante el registro de los directores de la serie [{$this->serieId}]: [{$e}]");
            mysqli_rollback($conn);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $created;
    }

    public function update($serieDirectorStatus): bool
    {
        $updated = false;

        $dbManager = new DBManager();

        try {

            $conn = $dbManager->getConnection();

            mysqli_begin_transaction($conn);

            mysqli_autocommit($conn, false);

            $stmt = $dbManager->queryPrepare($this->updateQuery());
            
            mysqli_stmt_bind_param($stmt, 'iiii', $this->directorId, $this->status, $this->serieId, $this->directorId);

            foreach ($serieDirectorStatus as $directorItemId => $statusItem) {

                $this->setdirectorId($directorItemId);

                $this->setStatus($statusItem);

                mysqli_stmt_execute($stmt);
            }

            $updated = mysqli_commit($conn);
        } catch (Exception $e) {
            error_log("Error durante el actualización de los directores de la serie [{$this->serieId}]: [{$e}]");
            mysqli_rollback($conn);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $updated;
    }

    /**
     * Get the value of directorId
     */
    public function getdirectorId()
    {
        return $this->directorId;
    }

    /**
     * Set the value of directorId
     *
     * @return  self
     */
    public function setdirectorId($directorId)
    {
        $this->directorId = $directorId;

        return $this;
    }

    /**
     * Get the value of serieId
     */
    public function getSerieId()
    {
        return $this->serieId;
    }

    /**
     * Set the value of serieId
     *
     * @return  self
     */
    public function setSerieId($serieId)
    {
        $this->serieId = $serieId;

        return $this;
    }

    /**
     * Get the value of status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    private function cleanConnection(mysqli_stmt $statement, DBManager $dbManager)
    {
        $statement->close();
        $dbManager->closeConnection();
        unset($dbManager);
    }

    private function selectAllQuery(): string
    {
        return "SELECT " . self::COLUMN_DIRECTOR_ID . "," . self::COLUMN_SERIE_ID . "," . self::COLUMN_STATUS . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_DIRECTOR_ID . "," . self::COLUMN_SERIE_ID . ") VALUES (?,?)";
    }

    private function updateQuery(): string
    {
       
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_DIRECTOR_ID . "= ?, " .  self::COLUMN_STATUS . "= ? WHERE " . self::COLUMN_SERIE_ID . "= ? AND " . self::COLUMN_DIRECTOR_ID . "= ?";
    }
}

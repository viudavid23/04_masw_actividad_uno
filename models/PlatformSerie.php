<?php
require_once('../../utils/DBManager.php');
class PlatformSerie
{
    const TABLE_NAME = 'platform_serie';
    const COLUMN_PLATFORM_ID = 'platform_id';
    const COLUMN_SERIE_ID = 'serie_id';
    const COLUMN_STATUS = 'status';

    private $platformId;
    private $serieId;
    private $status;

    public function __construct($platformId = null, $serieId = null, $status = null)
    {
        if ($platformId != null) {
            $this->platformId = $platformId;
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
            $platfformSerie = new PlatformSerie($item[self::COLUMN_PLATFORM_ID], $item[self::COLUMN_SERIE_ID], $item[self::COLUMN_STATUS]);
            array_push($listData, $platfformSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function getByPlatformId(): mixed
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnQuery(self::COLUMN_PLATFORM_ID));
        $stmt->bind_param('i', $this->platformId);
        $stmt->execute();
        $resultSet = $stmt->get_result();
        $listData = [];

        foreach ($resultSet as $item) {
            $platfformSerie = new PlatformSerie($item[self::COLUMN_PLATFORM_ID], $item[self::COLUMN_SERIE_ID], $item[self::COLUMN_STATUS]);
            array_push($listData, $platfformSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function save($platformIdsArray): bool
    {
        $created = false;

        $dbManager = new DBManager();

        try {

            $conn = $dbManager->getConnection();

            mysqli_begin_transaction($conn);

            mysqli_autocommit($conn, false);

            $stmt = $dbManager->queryPrepare($this->insertQuery());

            mysqli_stmt_bind_param($stmt, 'ii', $this->platformId, $this->serieId);

            foreach ($platformIdsArray as $platformItemId) {

                $this->setPlatformId($platformItemId);

                mysqli_stmt_execute($stmt);
            }

            $created = mysqli_commit($conn);
        } catch (Exception $e) {
            error_log("Error durante el registro de las plataformas de la serie [{$this->serieId}]: [{$e}]");
            mysqli_rollback($conn);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $created;
    }

    public function update($seriePlatformStatus): bool
    {
        $updated = false;

        $dbManager = new DBManager();

        try {

            $conn = $dbManager->getConnection();

            mysqli_begin_transaction($conn);

            mysqli_autocommit($conn, false);

            $stmt = $dbManager->queryPrepare($this->updateQuery());
            
            mysqli_stmt_bind_param($stmt, 'iiii', $this->platformId, $this->status, $this->serieId, $this->platformId);

            foreach ($seriePlatformStatus as $platformItemId => $statusItem) {

                $this->setPlatformId($platformItemId);

                $this->setStatus($statusItem);

                mysqli_stmt_execute($stmt);
            }

            $updated = mysqli_commit($conn);
        } catch (Exception $e) {
            error_log("Error durante el actualización de las plataformas de la serie [{$this->serieId}]: [{$e}]");
            mysqli_rollback($conn);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $updated;
    }

    /**
     * Get the value of platformId
     */
    public function getPlatformId()
    {
        return $this->platformId;
    }

    /**
     * Set the value of platformId
     *
     * @return  self
     */
    public function setPlatformId($platformId)
    {
        $this->platformId = $platformId;

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
        return "SELECT " . self::COLUMN_PLATFORM_ID . "," . self::COLUMN_SERIE_ID . "," . self::COLUMN_STATUS . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_PLATFORM_ID . "," . self::COLUMN_SERIE_ID . ") VALUES (?,?)";
    }

    private function updateQuery(): string
    {
       
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_PLATFORM_ID . "= ?, " .  self::COLUMN_STATUS . "= ? WHERE " . self::COLUMN_SERIE_ID . "= ? AND " . self::COLUMN_PLATFORM_ID . "= ?";
    }
}

<?php
require_once('../../utils/DBManager.php');
class ActorSerie
{
    const TABLE_NAME = 'actor_serie';
    const COLUMN_ACTOR_ID = 'actor_id';
    const COLUMN_SERIE_ID = 'serie_id';
    const COLUMN_STATUS = 'status';

    private $actorId;
    private $serieId;
    private $status;

    public function __construct($actorId = null, $serieId = null, $status = null)
    {
        if ($actorId != null) {
            $this->actorId = $actorId;
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
            $actorSerie = new ActorSerie($item[self::COLUMN_ACTOR_ID], $item[self::COLUMN_SERIE_ID], $item[self::COLUMN_STATUS]);
            array_push($listData, $actorSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function getByActorId(): mixed
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnQuery(self::COLUMN_ACTOR_ID));
        $stmt->bind_param('i', $this->actorId);
        $stmt->execute();
        $resultSet = $stmt->get_result();
        $listData = [];

        foreach ($resultSet as $item) {
            $actorSerie = new ActorSerie($item[self::COLUMN_ACTOR_ID], $item[self::COLUMN_SERIE_ID], $item[self::COLUMN_STATUS]);
            array_push($listData, $actorSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function save($actorIdsArray): bool
    {
        $created = false;

        $dbManager = new DBManager();

        try {

            $conn = $dbManager->getConnection();

            mysqli_begin_transaction($conn);

            mysqli_autocommit($conn, false);

            $stmt = $dbManager->queryPrepare($this->insertQuery());

            mysqli_stmt_bind_param($stmt, 'ii', $this->actorId, $this->serieId);

            foreach ($actorIdsArray as $actorItemId) {

                $this->setActorId($actorItemId);

                mysqli_stmt_execute($stmt);
            }

            $created = mysqli_commit($conn);
        } catch (Exception $e) {
            error_log("Error durante el registro de los actores de la serie [{$this->serieId}]: [{$e}]");
            mysqli_rollback($conn);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $created;
    }

    public function update($serieActorStatus): bool
    {
        $updated = false;

        $dbManager = new DBManager();

        try {

            $conn = $dbManager->getConnection();

            mysqli_begin_transaction($conn);

            mysqli_autocommit($conn, false);

            $stmt = $dbManager->queryPrepare($this->updateQuery());
            
            mysqli_stmt_bind_param($stmt, 'iiii', $this->actorId, $this->status, $this->serieId, $this->actorId);

            foreach ($serieActorStatus as $actorItemId => $statusItem) {

                $this->setActorId($actorItemId);

                $this->setStatus($statusItem);

                mysqli_stmt_execute($stmt);
            }

            $updated = mysqli_commit($conn);
        } catch (Exception $e) {
            error_log("Error durante el actualización de los actores de la serie [{$this->serieId}]: [{$e}]");
            mysqli_rollback($conn);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $updated;
    }

    /**
     * Get the value of actorId
     */
    public function getActorId()
    {
        return $this->actorId;
    }

    /**
     * Set the value of actorId
     *
     * @return  self
     */
    public function setActorId($actorId)
    {
        $this->actorId = $actorId;

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
        return "SELECT " . self::COLUMN_ACTOR_ID . "," . self::COLUMN_SERIE_ID . "," . self::COLUMN_STATUS . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_ACTOR_ID . "," . self::COLUMN_SERIE_ID . ") VALUES (?,?)";
    }

    private function updateQuery(): string
    {
       
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_ACTOR_ID . "= ?, " .  self::COLUMN_STATUS . "= ? WHERE " . self::COLUMN_SERIE_ID . "= ? AND " . self::COLUMN_ACTOR_ID . "= ?";
    }
}

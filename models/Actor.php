<?php

require_once('../../utils/DBManager.php');

class Actor
{
    const TABLE_NAME = 'actor';
    const COLUMN_ID = 'id';
    const COLUMN_STAGE_NAME = 'stage_name';
    const COLUMN_BIOGRAPHY = 'biography';
    const COLUMN_AWARDS = 'awards';
    const COLUMN_HEIGHT = 'height';
    const COLUMN_PERSON_ID = 'person_id';
    private $id;
    private $stageName;
    private $biography;
    private $awards;
    private $height;
    private $personId;

    public function __construct($id = null, $stageName = null, $biography = null, $awards = null, $height = null, $personId = null)
    {
        if ($id != null) {
            $this->id = $id;
        }
        if ($stageName != null) {
            $this->stageName = $stageName;
        }
        if ($biography != null) {
            $this->biography = $biography;
        }
        if ($awards != null) {
            $this->awards = $awards;
        }
        if ($height != null) {
            $this->height = $height;
        }
        if ($personId != null) {
            $this->personId = $personId;
        }
    }

    public function getAll(): mixed
    {
        $dbManager = new DBManager();
        $resultSet = $dbManager->queryExecute($this->selectAllQuery());
        $listData = [];

        foreach ($resultSet as $item) {
            $language = new Actor($item[self::COLUMN_ID], $item[self::COLUMN_STAGE_NAME], $item[self::COLUMN_BIOGRAPHY], $item[self::COLUMN_AWARDS], $item[self::COLUMN_HEIGHT], $item[self::COLUMN_PERSON_ID]);
            array_push($listData, $language);
        }

        $dbManager->closeConnection();
        unset($dbManager);

        return $listData;
    }

    public function getById(): mixed
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnQuery(self::COLUMN_ID));
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $result = $stmt->get_result();

        $actorSaved = null;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $actorSaved = new Actor($row[self::COLUMN_ID], $row[self::COLUMN_STAGE_NAME], $row[self::COLUMN_BIOGRAPHY], $row[self::COLUMN_AWARDS], $row[self::COLUMN_HEIGHT], $row[self::COLUMN_PERSON_ID]);
                break;
            }
        } else {
            $actorSaved = false;
        }
        
        $this->cleanConnection($stmt, $dbManager);

        return $actorSaved;
    }

    public function save(): bool
    {

        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->insertQuery());
        $stmt->bind_param('sssdi', $this->stageName, $this->biography, $this->awards, $this->height, $this->personId);
        $stmt->execute();

        $saved = ($stmt->affected_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $saved;
    }

    public function update(): bool
    {
    
        $dbManager = new DBManager();

        $actorSaved = $this->getById($this->id);

        if(!$this->isDataDifferent($actorSaved)){
            return true;
        }

        $stmt = $dbManager->queryPrepare($this->updateQuery());
        $stmt->bind_param('sssdii', $this->stageName, $this->biography, $this->awards, $this->height, $this->personId, $this->id);
        $stmt->execute();

        $updated = ($stmt->affected_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $updated;
    }

    public function delete(): bool
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->deleteQuery());
        $stmt->bind_param('i', $this->id);
        $stmt->execute();

        $saved = ($stmt->affected_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $saved;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of stageName
     */ 
    public function getStageName()
    {
        return $this->stageName;
    }

    /**
     * Set the value of stageName
     *
     * @return  self
     */ 
    public function setStageName($stageName)
    {
        $this->stageName = $stageName;

        return $this;
    }

    /**
     * Get the value of biography
     */ 
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * Set the value of biography
     *
     * @return  self
     */ 
    public function setBiography($biography)
    {
        $this->biography = $biography;

        return $this;
    }

    /**
     * Get the value of awards
     */ 
    public function getAwards()
    {
        return $this->awards;
    }

    /**
     * Set the value of awards
     *
     * @return  self
     */ 
    public function setAwards($awards)
    {
        $this->awards = $awards;

        return $this;
    }

    /**
     * Get the value of height
     */ 
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the value of height
     *
     * @return  self
     */ 
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get the value of personId
     */ 
    public function getPersonId()
    {
        return $this->personId;
    }

    /**
     * Set the value of personId
     *
     * @return  self
     */ 
    public function setPersonId($personId)
    {
        $this->personId = $personId;

        return $this;
    }

    private function isDataDifferent($currentData): bool
    {
        return strtolower($currentData->getStageName()) !== strtolower($this->getStageName()) ||
        strtolower($currentData->getBiography()) !== strtolower($this->getBiography()) ||
            $currentData->getAwards() !== $this->getAwards() ||
            $currentData->getHeight() !== $this->getHeight() ||
            $currentData->getPersonId() !== $this->getPersonId();
    }

    private function cleanConnection(mysqli_stmt $statement, DBManager $dbManager)
    {
        $statement->close();
        $dbManager->closeConnection();
        unset($dbManager);
    }

    private function selectAllQuery(): string
    {
        return "SELECT " . self::COLUMN_ID . "," . self::COLUMN_STAGE_NAME . "," . self::COLUMN_BIOGRAPHY . "," . self::COLUMN_AWARDS . "," . self::COLUMN_HEIGHT . "," . self::COLUMN_PERSON_ID . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_STAGE_NAME . "," . self::COLUMN_BIOGRAPHY . "," . self::COLUMN_AWARDS . "," . self::COLUMN_HEIGHT . "," . self::COLUMN_PERSON_ID . ") VALUES (?,?,?,?,?)";
    }

    private function updateQuery(): string
    {
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_STAGE_NAME . "= ? ," .  self::COLUMN_BIOGRAPHY . "= ? ," .  self::COLUMN_AWARDS . "= ? ," .  self::COLUMN_HEIGHT . "= ? ," . self::COLUMN_PERSON_ID . "= ? WHERE " . self::COLUMN_ID . "= ?";
    }

    private function deleteQuery(): string
    {
        return "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COLUMN_ID . "= ?";
    }
}

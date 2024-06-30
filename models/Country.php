<?php

require_once('../../utils/DBManager.php');

class Country
{
    const TABLE_NAME = 'country';
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'name';
    const COLUMN_DEMONYM = 'demonym';
    const COLUMN_LANGUAGE_ID = 'language_id';
    private $id;
    private $name;
    private $demonym;
    private $languageId;

    public function __construct($id = null, $name = null, $demonym = null, $languageId = null)
    {
        if ($id != null) {
            $this->id = $id;
        }
        if ($name != null) {
            $this->name = $name;
        }
        if ($demonym != null) {
            $this->demonym = $demonym;
        }
        if ($languageId != null) {
            $this->languageId = $languageId;
        }
    }

    public function getAll(): array
    {
        $dbManager = new DBManager();
        $resultSet = $dbManager->queryExecute($this->selectAllQuery());
        $listData = [];

        foreach ($resultSet as $item) {
            $country = new Country($item[self::COLUMN_ID], $item[self::COLUMN_NAME], $item[self::COLUMN_DEMONYM], $item[self::COLUMN_LANGUAGE_ID]);
            array_push($listData, $country);
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

        $countrySaved = null;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $countrySaved = new Country($row[self::COLUMN_ID], $row[self::COLUMN_NAME], $row[self::COLUMN_DEMONYM], $row[self::COLUMN_LANGUAGE_ID]);
                break;
            }
        } else {
            $countrySaved = false;
        }
        
        $this->cleanConnection($stmt, $dbManager);

        return $countrySaved;
    }
/*
    public function save(): bool
    {

        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->insertQuery());
        $stmt->bind_param('sssi', $this->firstName, $this->lastName, $this->birthdate, $this->countryId);
        $stmt->execute();

        $saved = ($stmt->affected_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $saved;
    }

    public function update(): bool
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->updateQuery());
        $stmt->bind_param('sssii', $this->firstName, $this->lastName, $this->birthdate, $this->countryId, $this->id);
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
*/

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
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of demonym
     */ 
    public function getDemonym()
    {
        return $this->demonym;
    }

    /**
     * Set the value of demonym
     *
     * @return  self
     */ 
    public function setDemonym($demonym)
    {
        $this->demonym = $demonym;

        return $this;
    }

    /**
     * Get the value of languageId
     */ 
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * Set the value of languageId
     *
     * @return  self
     */ 
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;

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
        return "SELECT " . self::COLUMN_ID . "," . self::COLUMN_NAME . "," . self::COLUMN_DEMONYM . "," . self::COLUMN_LANGUAGE_ID . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_NAME . "," . self::COLUMN_DEMONYM . "," . self::COLUMN_LANGUAGE_ID . ") VALUES (?,?,?)";
    }

    private function updateQuery(): string
    {
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_NAME . "= ? ," .  self::COLUMN_DEMONYM . "= ? ," .  self::COLUMN_LANGUAGE_ID . "= ? WHERE " . self::COLUMN_ID . "= ?";
    }

    private function deleteQuery(): string
    {
        return "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COLUMN_ID . "= ?";
    }
}

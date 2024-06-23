<?php
require_once('../../utils/DBManager.php');
class Language
{
    const TABLE_NAME = 'language';
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'name';
    const COLUMN_ISO_CODE = 'iso_code';
    private $id;
    private $name;
    private $isoCode;

    public function __construct($id = null, $name = null, $isoCode = null)
    {

        if ($id != null) {
            $this->id = $id;
        }
        if ($name != null) {
            $this->name = $name;
        }
        if ($isoCode != null) {
            $this->isoCode = $isoCode;
        }
    }

    public function getAll(): array
    {
        $dbManager = new DBManager();
        $resultSet = $dbManager->queryExecute($this->selectAllQuery());
        $listData = [];

        foreach ($resultSet as $item) {
            $language = new Language($item[self::COLUMN_ID], $item[self::COLUMN_NAME], $item[self::COLUMN_ISO_CODE]);
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

        $languageSaved = null;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $languageSaved = new Language($row[self::COLUMN_ID], $row[self::COLUMN_NAME], $row[self::COLUMN_ISO_CODE]);
                break;
            }
        } else {
            $languageSaved = false;
        }
        
        $this->cleanConnection($stmt, $dbManager);

        return $languageSaved;
    }

    public function getByIsoCode(): bool
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnQuery(self::COLUMN_ISO_CODE));
        $stmt->bind_param('s', $this->isoCode);
        $stmt->execute();
        $result = $stmt->get_result();

        $languageSaved = ($result->num_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $languageSaved;
    }

    public function save(): bool
    {

        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->insertQuery());
        $stmt->bind_param('ss', $this->name, $this->isoCode);
        $stmt->execute();

        $saved = ($stmt->affected_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $saved;
    }

    public function update(): bool
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->updateQuery());
        $stmt->bind_param('ssi', $this->name, $this->isoCode, $this->id);
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
     * Get the value of isoCode
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * Set the value of isoCode
     *
     * @return  self
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;

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
        return "SELECT " . self::COLUMN_ID . "," . self::COLUMN_NAME . "," . self::COLUMN_ISO_CODE . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_NAME . "," . self::COLUMN_ISO_CODE . ") VALUES (?,?)";
    }

    private function updateQuery(): string
    {
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_NAME . "= ? ," . self::COLUMN_ISO_CODE . "= ? WHERE " . self::COLUMN_ID . "= ?";
    }

    private function deleteQuery(): string
    {
        return "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COLUMN_ID . "= ?";
    }
}

<?php

require_once('../../utils/DBManager.php');

class Person
{
    const TABLE_NAME = 'person';
    const COLUMN_ID = 'id';
    const COLUMN_FIRST_NAME = 'first_name';
    const COLUMN_LAST_NAME = 'last_name';
    const COLUMN_BIRTHDAY = 'birthdate';
    const COLUMN_COUNTRY_ID = 'country_id';
    private $id;
    private $firstName;
    private $lastName;
    private $birthdate;
    private $countryId;

    public function __construct($id = null, $firstName = null, $lastName = null, $birthdate = null, $countryId = null)
    {
        if ($id != null) {
            $this->id = $id;
        }
        if ($firstName != null) {
            $this->firstName = $firstName;
        }
        if ($lastName != null) {
            $this->lastName = $lastName;
        }
        if ($birthdate != null) {
            $this->birthdate = $birthdate;
        }
        if ($countryId != null) {
            $this->countryId = $countryId;
        }
    }

    public function getById(): mixed
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnQuery(self::COLUMN_ID));
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $result = $stmt->get_result();

        $personSaved = null;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $personSaved = new Person($row[self::COLUMN_ID], $row[self::COLUMN_FIRST_NAME], $row[self::COLUMN_LAST_NAME], $row[self::COLUMN_BIRTHDAY], $row[self::COLUMN_COUNTRY_ID]);
                break;
            }
        } else {
            $personSaved = false;
        }

        $this->cleanConnection($stmt, $dbManager);

        return $personSaved;
    }

    public function save(): mixed
    {
        $saved = false;
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->insertQuery());
        $stmt->bind_param('sssi', $this->firstName, $this->lastName, $this->birthdate, $this->countryId);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;

        if ($affectedRows > 0) {
            $insertId = $stmt->insert_id;
            $saved = new Person($insertId, $this->firstName, $this->lastName, $this->birthdate, $this->countryId);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $saved;
    }

    public function update(): bool
    {
        $dbManager = new DBManager();

        $personSaved = $this->getById($this->id);

        if(!$this->isDataDifferent($personSaved)){
            return true;
        }

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

        $deleted = ($stmt->affected_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $deleted;
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
     * Get the value of firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the value of firstName
     *
     * @return  self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the value of lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the value of lastName
     *
     * @return  self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the value of birthdate
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set the value of birthdate
     *
     * @return  self
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get the value of countryId
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * Set the value of countryId
     *
     * @return  self
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;

        return $this;
    }

    private function isDataDifferent($currentData): bool
    {

        return strtolower($currentData->getFirstName()) !== strtolower($this->getFirstName()) ||
            strtolower($currentData->getLastName()) !== strtolower($this->getLastName()) ||
            $currentData->getBirthdate() !== $this->getBirthdate() ||
            (int) $currentData->getCountryId() !== (int) $this->getCountryId();
    }

    private function cleanConnection(mysqli_stmt $statement, DBManager $dbManager)
    {
        $statement->close();
        $dbManager->closeConnection();
        unset($dbManager);
    }

    private function selectAllQuery(): string
    {
        return "SELECT " . self::COLUMN_ID . "," . self::COLUMN_FIRST_NAME . "," . self::COLUMN_LAST_NAME . "," . self::COLUMN_BIRTHDAY . "," . self::COLUMN_COUNTRY_ID . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_FIRST_NAME . "," . self::COLUMN_LAST_NAME . "," . self::COLUMN_BIRTHDAY . "," . self::COLUMN_COUNTRY_ID . ") VALUES (?,?,?,?)";
    }

    private function updateQuery(): string
    {
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_FIRST_NAME . "= ? ," .  self::COLUMN_LAST_NAME . "= ? ," .  self::COLUMN_BIRTHDAY . "= ? ," . self::COLUMN_COUNTRY_ID . "= ? WHERE " . self::COLUMN_ID . "= ?";
    }

    private function deleteQuery(): string
    {
        return "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COLUMN_ID . "= ?";
    }
}

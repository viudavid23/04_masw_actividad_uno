<?php

require_once('../../utils/DBManager.php');

class Director
{
    const TABLE_NAME = 'director';
    const COLUMN_ID = 'id';
    const COLUMN_BEGINNING_CAREER = 'beginning_career';
    const COLUMN_ACTIVE_YEARS = 'active_years';
    const COLUMN_BIOGRAPHY = 'biography';
    const COLUMN_AWARDS = 'awards';
    const COLUMN_PERSON_ID = 'person_id';
    private $id;
    private $beginningCareer;
    private $activeYears;
    private $biography;
    private $awards;
    private $personId;

    public function __construct($id = null, $beginningCareer = null, $activeYears = null, $biography = null, $awards = null, $personId = null)
    {
        if ($id != null) {
            $this->id = $id;
        }
        if ($beginningCareer != null) {
            $this->beginningCareer = $beginningCareer;
        }
        if ($activeYears != null) {
            $this->activeYears = $activeYears;
        }
        if ($biography != null) {
            $this->biography = $biography;
        }
        if ($awards != null) {
            $this->awards = $awards;
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
            $director = new Director($item[self::COLUMN_ID], $item[self::COLUMN_BEGINNING_CAREER], $item[self::COLUMN_ACTIVE_YEARS], $item[self::COLUMN_BIOGRAPHY], $item[self::COLUMN_AWARDS], $item[self::COLUMN_PERSON_ID]);
            array_push($listData, $director);
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

        $directorSaved = null;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $directorSaved = new Director($row[self::COLUMN_ID], $row[self::COLUMN_BEGINNING_CAREER], $row[self::COLUMN_ACTIVE_YEARS], $row[self::COLUMN_BIOGRAPHY], $row[self::COLUMN_AWARDS], $row[self::COLUMN_PERSON_ID]);
                break;
            }
        } else {
            $directorSaved = false;
        }

        $this->cleanConnection($stmt, $dbManager);

        return $directorSaved;
    }

    public function save(): bool
    {

        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->insertQuery());
        $stmt->bind_param('sissi', $this->beginningCareer, $this->activeYears, $this->biography, $this->awards, $this->personId);
        $stmt->execute();

        $saved = ($stmt->affected_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $saved;
    }

    public function update(): bool
    {

        $dbManager = new DBManager();

        $actorSaved = $this->getById($this->id);

        if (!$this->isDataDifferent($actorSaved)) {
            return true;
        }

        $stmt = $dbManager->queryPrepare($this->updateQuery());
        $stmt->bind_param('sissii', $this->beginningCareer, $this->activeYears, $this->biography, $this->awards, $this->personId, $this->id);
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

    private function isDataDifferent($currentData): bool
    {
        return $currentData->getBeginningCareer() !== $this->getBeginningCareer() ||
            $currentData->getActiveYears() !== (int)$this->getActiveYears() ||
            $currentData->getBiography() !== $this->getBiography() ||
            $currentData->getAwards() !== $this->getAwards() ||
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
        return "SELECT " . self::COLUMN_ID . "," . self::COLUMN_BEGINNING_CAREER . "," . self::COLUMN_ACTIVE_YEARS . "," . self::COLUMN_BIOGRAPHY . "," . self::COLUMN_AWARDS . "," . self::COLUMN_PERSON_ID . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_BEGINNING_CAREER . "," . self::COLUMN_ACTIVE_YEARS . "," . self::COLUMN_BIOGRAPHY . "," . self::COLUMN_AWARDS . "," . self::COLUMN_PERSON_ID . ") VALUES (?,?,?,?,?)";
    }

    private function updateQuery(): string
    {
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_BEGINNING_CAREER . "= ? ," .  self::COLUMN_ACTIVE_YEARS . "= ? ," .  self::COLUMN_BIOGRAPHY . "= ? ," .  self::COLUMN_AWARDS . "= ? ," . self::COLUMN_PERSON_ID . "= ? WHERE " . self::COLUMN_ID . "= ?";
    }

    private function deleteQuery(): string
    {
        return "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COLUMN_ID . "= ?";
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
     * Get the value of beginningCareer
     */
    public function getBeginningCareer()
    {
        return $this->beginningCareer;
    }

    /**
     * Set the value of beginningCareer
     *
     * @return  self
     */
    public function setBeginningCareer($beginningCareer)
    {
        $this->beginningCareer = $beginningCareer;

        return $this;
    }

    /**
     * Get the value of activeYears
     */
    public function getActiveYears()
    {
        return $this->activeYears;
    }

    /**
     * Set the value of activeYears
     *
     * @return  self
     */
    public function setActiveYears($activeYears)
    {
        $this->activeYears = $activeYears;

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
}

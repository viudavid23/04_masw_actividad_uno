<?php
require_once('../../utils/DBManager.php');
class Platform
{
    const TABLE_NAME = 'platform';
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'name';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_RELEASE_DATE = 'release_date';
    const COLUMN_LOGO = 'logo';
    private $id;
    private $name;
    private $description;
    private $releaseDate;
    private $logo;

    public function __construct($id = null, $name = null, $description = null, $releaseDate = null, $logo = null)
    {
        if ($id != null) {
            $this->id = $id;
        }
        if ($name != null) {
            $this->name = $name;
        }
        if ($description != null) {
            $this->description = $description;
        }
        if ($releaseDate != null) {
            $this->releaseDate = $releaseDate;
        }
        if ($logo != null) {
            $this->logo = $logo;
        }
    }

    public function getAll(): array
    {
        $dbManager = new DBManager();
        $resultSet = $dbManager->queryExecute($this->selectAllQuery());
        $listData = [];

        foreach ($resultSet as $item) {
            $platfform = new Platform($item[self::COLUMN_ID], $item[self::COLUMN_NAME], $item[self::COLUMN_DESCRIPTION], $item[self::COLUMN_RELEASE_DATE], $item[self::COLUMN_LOGO]);
            array_push($listData, $platfform);
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

        $platformSaved = null;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $platformSaved = new Platform($row[self::COLUMN_ID], $row[self::COLUMN_NAME], $row[self::COLUMN_DESCRIPTION], $row[self::COLUMN_RELEASE_DATE], $row[self::COLUMN_LOGO]);
                break;
            }
        } else {
            $platformSaved = false;
        }

        $this->cleanConnection($stmt, $dbManager);

        return $platformSaved;
    }

    public function checkByName(): bool
    {

        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnLikeQuery(self::COLUMN_NAME));
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $result = $stmt->get_result();

        $platformSaved = $result->num_rows > 0 ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $platformSaved;
    }

    public function save(): bool
    {

        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->insertQuery());
        $stmt->bind_param('ssss', $this->name, $this->description, $this->releaseDate, $this->logo);
        $stmt->execute();

        $saved = ($stmt->affected_rows > 0) ? true : false;

        $this->cleanConnection($stmt, $dbManager);

        return $saved;
    }

    public function update(): bool
    {
        $dbManager = new DBManager();

        $platformSaved = $this->getById($this->id);

        if ($platformSaved && !$this->compareCurrentData($platformSaved)) {
            return true;
        }

        $alreadySaved = $this->checkByName($this->getName());

        if ($alreadySaved) {
            error_log("Database exception: El Nombre de la plataforma ya se encuentra en uso - Name [{$this->name}]");
            return false;
        }

        $stmt = $dbManager->queryPrepare($this->updateQuery());
        $stmt->bind_param('ssssi', $this->name, $this->description, $this->releaseDate, $this->logo, $this->id);
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
     * Get the value of description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of releaseDate
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Set the value of releaseDate
     *
     * @return  self
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    /**
     * Get the value of logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set the value of logo
     *
     * @return  self
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    private function compareCurrentData($currentData): bool
    {
        return strtolower($currentData->getName()) !== strtolower($this->getName()) ||
            strtolower($currentData->getDescription()) !== strtolower($this->getDescription()) ||
            $currentData->getReleaseDate() !== $this->getReleaseDate() ||
            $currentData->getLogo() !== $this->getLogo();
    }

    private function cleanConnection(mysqli_stmt $statement, DBManager $dbManager)
    {
        $statement->close();
        $dbManager->closeConnection();
        unset($dbManager);
    }

    private function selectAllQuery(): string
    {
        return "SELECT " . self::COLUMN_ID . "," . self::COLUMN_NAME . "," . self::COLUMN_DESCRIPTION . "," . self::COLUMN_RELEASE_DATE . "," . self::COLUMN_LOGO . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function selectByColumnLikeQuery($column): string
    {
        return "SELECT $column  FROM " . self::TABLE_NAME . " WHERE $column LIKE CONCAT(?, '%')";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_NAME . "," . self::COLUMN_DESCRIPTION . "," . self::COLUMN_RELEASE_DATE . "," . self::COLUMN_LOGO . ") VALUES (?,?,?,?)";
    }

    private function updateQuery(): string
    {
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_NAME . "= ? ," . self::COLUMN_DESCRIPTION . "= ? ," . self::COLUMN_RELEASE_DATE . "= ? ," . self::COLUMN_LOGO  . "= ? WHERE " . self::COLUMN_ID . "= ?";
    }

    private function deleteQuery(): string
    {
        return "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COLUMN_ID . "= ?";
    }
}

<?php
require_once('../../utils/DBManager.php');
class Serie
{
    const TABLE_NAME = 'serie';
    const COLUMN_ID = 'id';
    const COLUMN_TITLE = 'title';
    const COLUMN_SYNOPSIS = 'synopsis';
    const COLUMN_RELEASE_DATE = 'release_date';
    private $id;
    private $title;
    private $synopsis;
    private $releaseDate;

    public function __construct($id = null, $title = null, $synopsis = null, $releaseDate = null)
    {
        if ($id != null) {
            $this->id = $id;
        }
        if ($title != null) {
            $this->title = $title;
        }
        if ($synopsis != null) {
            $this->synopsis = $synopsis;
        }
        if ($releaseDate != null) {
            $this->releaseDate = $releaseDate;
        }
    }

    public function getAll(): array
    {
        $dbManager = new DBManager();
        $resultSet = $dbManager->queryExecute($this->selectAllQuery());
        $listData = [];

        foreach ($resultSet as $item) {
            $serie = new Serie($item[self::COLUMN_ID], $item[self::COLUMN_TITLE], $item[self::COLUMN_SYNOPSIS], $item[self::COLUMN_RELEASE_DATE]);
            array_push($listData, $serie);
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

        $serieSaved = null;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $serieSaved = new Serie($row[self::COLUMN_ID], $row[self::COLUMN_TITLE], $row[self::COLUMN_SYNOPSIS], $row[self::COLUMN_RELEASE_DATE]);
                break;
            }
        } else {
            $serieSaved = false;
        }

        $this->cleanConnection($stmt, $dbManager);

        return $serieSaved;
    }

    public function save(): self
    {
        $dbManager = new DBManager();

        $stmt = $dbManager->queryPrepare($this->insertQuery());
        $stmt->bind_param('sss', $this->title, $this->synopsis, $this->releaseDate);
        $stmt->execute();

        if($stmt->affected_rows > 0){
            $this->id = $dbManager->getConnection()->insert_id;
        }
        
        $this->cleanConnection($stmt, $dbManager);

        return $this;
    }

    public function update(): bool
    {
        $dbManager = new DBManager();

        $serieSaved = $this->getById($this->id);

        if(!$serieSaved){
            return false;
        }

        if (!$this->compareCurrentData($serieSaved)) {
            return true;
        }

        $stmt = $dbManager->queryPrepare($this->updateQuery());
        $stmt->bind_param('sssi', $this->title, $this->synopsis, $this->releaseDate, $this->id);
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
     * Get the value of title
     */ 
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @return  self
     */ 
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of synopsis
     */ 
    public function getSynopsis()
    {
        return $this->synopsis;
    }

    /**
     * Set the value of synopsis
     *
     * @return  self
     */ 
    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;

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
     * Función para determinar si los datos recibidos son iguales a los datos almacenados en el proceso de actualización.
     * Si la data es igual no se continua con el proceso.
     * 
     * @return boolean true si los datos son diferentes y false si los datos son iguales
     */
    private function compareCurrentData($currentData): bool
    {
        return strtolower($currentData->getTitle()) !== strtolower($this->getTitle()) ||
            strtolower($currentData->getSynopsis()) !== strtolower($this->getSynopsis()) ||
            $currentData->getReleaseDate() !== $this->getReleaseDate();
    }

    private function cleanConnection(mysqli_stmt $statement, DBManager $dbManager)
    {
        $statement->close();
        $dbManager->closeConnection();
        unset($dbManager);
    }

    private function selectAllQuery(): string
    {
        return "SELECT " . self::COLUMN_ID . "," . self::COLUMN_TITLE . "," . self::COLUMN_SYNOPSIS . "," . self::COLUMN_RELEASE_DATE . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_TITLE . "," . self::COLUMN_SYNOPSIS . "," . self::COLUMN_RELEASE_DATE .  ") VALUES (?,?,?)";
    }

    private function updateQuery(): string
    {
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_TITLE . "= ? ," . self::COLUMN_SYNOPSIS . "= ? ," . self::COLUMN_RELEASE_DATE . "= ? WHERE " . self::COLUMN_ID . "= ?";
    }

    private function deleteQuery(): string
    {
        return "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::COLUMN_ID . "= ?";
    }
}

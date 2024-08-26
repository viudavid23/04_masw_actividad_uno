<?php
require_once('../../utils/DBManager.php');
class LanguageSerie
{
    const TABLE_NAME = 'language_serie';
    const COLUMN_SERIE_ID = 'serie_id';
    const COLUMN_LANGUAGE_ID = 'language_id';
    const COLUMN_AUDIO = 'audio';
    const COLUMN_SUBTITLE = 'subtitle';

    private $serieId;
    private $languageId;
    private $audio;
    private $subtitle;

    public function __construct($serieId = null, $languageId = null, $audio = null, $subtitle = null)
    {
        if ($serieId != null) {
            $this->serieId = $serieId;
        }
        if ($languageId != null) {
            $this->languageId = $languageId;
        }
        if ($audio != null) {
            $this->audio = $audio;
        }
        if ($subtitle != null) {
            $this->subtitle = $subtitle;
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
            $languageSerie = new LanguageSerie($item[self::COLUMN_SERIE_ID], $item[self::COLUMN_LANGUAGE_ID], $item[self::COLUMN_AUDIO], $item[self::COLUMN_SUBTITLE]);
            array_push($listData, $languageSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function getByLanguageId(): mixed
    {
        $dbManager = new DBManager();
        $stmt = $dbManager->queryPrepare($this->selectByColumnQuery(self::COLUMN_LANGUAGE_ID));
        $stmt->bind_param('i', $this->languageId);
        $stmt->execute();
        $resultSet = $stmt->get_result();
        $listData = [];

        foreach ($resultSet as $item) {
            $languageSerie = new LanguageSerie($item[self::COLUMN_SERIE_ID], $item[self::COLUMN_LANGUAGE_ID], $item[self::COLUMN_AUDIO], $item[self::COLUMN_SUBTITLE]);
            array_push($listData, $languageSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function getBySerieIdAndComponents()
    {
        $dbManager = new DBManager();
       
        if ($this->audio != null) {
            $stmt = $dbManager->queryPrepare($this->selectByColumnAndAudioQuery(self::COLUMN_SERIE_ID));
            $stmt->bind_param('ii', $this->serieId, $this->audio);
        }

        if ($this->subtitle != null) {
            $stmt = $dbManager->queryPrepare($this->selectByColumnAndSubtitleQuery(self::COLUMN_SERIE_ID));
            $stmt->bind_param('ii', $this->serieId, $this->subtitle);
        }
    
        $stmt->execute();
        $resultSet = $stmt->get_result();
        $listData = [];

        foreach ($resultSet as $item) {
            $languageSerie = new LanguageSerie($item[self::COLUMN_SERIE_ID], $item[self::COLUMN_LANGUAGE_ID], $item[self::COLUMN_AUDIO], $item[self::COLUMN_SUBTITLE]);
            array_push($listData, $languageSerie);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $listData;
    }

    public function save($languageIdsArray): bool
    {
        $created = false;

        $dbManager = new DBManager();

        try {

            $conn = $dbManager->getConnection();

            mysqli_begin_transaction($conn);

            mysqli_autocommit($conn, false);

            $stmt = $dbManager->queryPrepare($this->insertQuery());

            mysqli_stmt_bind_param($stmt, 'iiii', $this->serieId, $this->languageId, $this->audio, $this->subtitle);

            foreach ($languageIdsArray as $languageItemId => $languageComponents) {
                
                $this->setLanguageId($languageItemId);

                foreach ($languageComponents as $languageComponentItem => $languageComponentValue) {
                    if ($languageComponentItem == self::COLUMN_AUDIO) {
                        $this->setAudio($languageComponentValue);
                    }
                    if ($languageComponentItem == self::COLUMN_SUBTITLE) {
                        $this->setSubtitle($languageComponentValue);
                    }
                }
               
                mysqli_stmt_execute($stmt);
            }

            $created = mysqli_commit($conn);
        } catch (Exception $e) {
            error_log("Error durante el registro de los idiomas de audio y subtitulos de la serie [{$this->serieId}]: [{$e}]");
            mysqli_rollback($conn);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $created;
    }

    public function update($languageIdsArray): bool
    {
        $updated = false;

        $dbManager = new DBManager();

        try {

            $conn = $dbManager->getConnection();

            mysqli_begin_transaction($conn);

            mysqli_autocommit($conn, false);

            $stmt = $dbManager->queryPrepare($this->updateQuery());
            
            mysqli_stmt_bind_param($stmt, 'iiiiii', $this->serieId, $this->languageId, $this->audio, $this->subtitle, $this->serieId, $this->languageId);

            foreach ($languageIdsArray as $languageItemId => $languageComponents) {
                
                $this->setLanguageId($languageItemId);

                foreach ($languageComponents as $languageComponentItem => $languageComponentValue) {
                    if ($languageComponentItem == self::COLUMN_AUDIO) {
                        $this->setAudio($languageComponentValue);
                    }
                    if ($languageComponentItem == self::COLUMN_SUBTITLE) {
                        $this->setSubtitle($languageComponentValue);
                    }
                }
               
                mysqli_stmt_execute($stmt);
            }

            $updated = mysqli_commit($conn);
        } catch (Exception $e) {
            error_log("Error durante el actualización de los idiomas de audio y subtitulos de la serie [{$this->serieId}]: [{$e}]");
            mysqli_rollback($conn);
        }

        $this->cleanConnection($stmt, $dbManager);

        return $updated;
    }


    private function cleanConnection(mysqli_stmt $statement, DBManager $dbManager)
    {
        $statement->close();
        $dbManager->closeConnection();
        unset($dbManager);
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

    /**
     * Get the value of audio
     */ 
    public function getAudio()
    {
        return $this->audio;
    }

    /**
     * Set the value of audio
     *
     * @return  self
     */ 
    public function setAudio($audio)
    {
        $this->audio = $audio;

        return $this;
    }

    /**
     * Get the value of subtitle
     */ 
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set the value of subtitle
     *
     * @return  self
     */ 
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    private function selectAllQuery(): string
    {
        return "SELECT " . self::COLUMN_SERIE_ID . "," . self::COLUMN_LANGUAGE_ID . "," . self::COLUMN_AUDIO . "," . self::COLUMN_SUBTITLE . " FROM " . self::TABLE_NAME;
    }

    private function selectByColumnQuery($column): string
    {
        return $this->selectAllQuery() . " WHERE $column = ?";
    }

    private function selectByColumnAndAudioQuery($column): string
    {
        return $this->selectByColumnQuery($column) . " AND " . self::COLUMN_AUDIO . "= ?";
    }

    private function selectByColumnAndSubtitleQuery($column): string
    {
        return $this->selectByColumnQuery($column) . " AND " . self::COLUMN_SUBTITLE . " = ?";
    }

    private function insertQuery(): string
    {
        return "INSERT INTO " . self::TABLE_NAME . "(" .  self::COLUMN_SERIE_ID . "," . self::COLUMN_LANGUAGE_ID . "," . self::COLUMN_AUDIO . "," . self::COLUMN_SUBTITLE . ") VALUES (?,?,?,?)";
    }

    private function updateQuery(): string
    {
       
        return "UPDATE " . self::TABLE_NAME . " SET " .  self::COLUMN_SERIE_ID . "= ?, " . self::COLUMN_LANGUAGE_ID . "= ?, " . self::COLUMN_AUDIO . "= ?, " . self::COLUMN_SUBTITLE . "= ? WHERE " . self::COLUMN_SERIE_ID . "= ? AND " . self::COLUMN_LANGUAGE_ID . "= ?";
    }
}

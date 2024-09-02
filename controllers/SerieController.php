<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/Serie.php');
require_once('exceptions/RecordNotFoundException.php');
require_once('validations/CommonValidation.php');
require_once('validations/SerieValidation.php');
require_once('PlatformSerieController.php');
require_once('ActorSerieController.php');
require_once('DirectorSerieController.php');
require_once('LanguageSerieController.php');
class SerieController
{
    const SERIE_TITLE = 'title';
    const SERIE_SYNOPSIS = 'synopsis';
    const SERIE_RELEASE_DATE = 'release_date';
    const SERIE_PLATFORMS = 'platforms';
    const SERIE_ACTORS = 'actors';
    const SERIE_DIRECTORS = 'directors';
    const SERIE_AUDIO_LANGUAGES = 'audio_languages';
    const SERIE_SUBTITLE_LANGUAGES = 'subtitle_languages';

    function showAll(): array
    {
        $serieObjectArray = [];

        $serieModel = new Serie();
        $serieModelList = $serieModel->getAll();

        foreach ($serieModelList as $serieItem) {
            $serieObject = $this->makeSerie($serieItem);
            array_push($serieObjectArray, $serieObject);
        }

        return $serieObjectArray;
    }

    function showById($id): mixed
    {
        try {
            $this->checkValidIdDataType($id);

            $model = new Serie($id);
            $serieSaved = $model->getById();

            if (!$serieSaved) {
                error_log("[SerieController] [Data Error] ID de la serie no encontrado en la tabla serie de la base de datos - [{$id}]");
                throw new RecordNotFoundException("Serie [{$id}] no registrada");
            }

            return $this->makeSerie($serieSaved);
        } catch (RecordNotFoundException $e) {
            error_log("[SerieController] [Record Not Found Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setWarningMessage($e->getMessage());
            return false;
        } catch (InvalidArgumentException $e) {
            error_log("[SerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setErrorMessage($e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[SerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setErrorMessage($e->getMessage());
            return false;
        }
    }

    function create($serieData): bool
    {
        try {

            $this->checkValidInputFields($serieData);

            $title = strtoupper($serieData[self::SERIE_TITLE]);
            $synopsis = strtoupper($serieData[self::SERIE_SYNOPSIS]);
            $releaseDate = $serieData[self::SERIE_RELEASE_DATE];

            $serieModel = new Serie(null, $title, $synopsis, $releaseDate);
            $serieSaved = $serieModel->save();

            if ($serieSaved->getId() !== null) {

                $serieId = $serieSaved->getId();

                $serieComponentsCreated = $this->createSerieComponents($serieId, $serieData);

                if (!$serieComponentsCreated) {

                    $serieModel->delete($serieId);

                    error_log("[SerieController] [Dependency Error] Falló al actualizar en la tabla serie - id: [{$serieId}] title: [{$title}] synopsis: [{$synopsis}] release_date: [{$releaseDate}] platform ids: [" . implode(',', $serieData[self::SERIE_PLATFORMS]) . "] actor ids: [" . implode(',', $serieData[self::SERIE_ACTORS]) . "] director ids: [" . implode(',', $serieData[self::SERIE_DIRECTORS]) . "] audio language ids: [" . implode(',', $serieData[self::SERIE_AUDIO_LANGUAGES]) . "] subtitle language ids: [" . implode(',', $serieData[self::SERIE_SUBTITLE_LANGUAGES]) . "]");
                    throw new RuntimeException("La Serie [{$title}] no se ha creado correctamente.", Constants::FAILED_DEPENDENCY_CODE);
                } else {

                    Utilities::setSuccessMessage("Serie [{$title}] creada correctamente.");
                    return true;
                }
            }

            error_log("[SerieController] [Data Error] Falló al guardar en la tabla serie - Titulo: [{$title}] Sinopsis: [{$synopsis}] Fecha Lanzamiento: [{$releaseDate}]");
            throw new RuntimeException("La Serie [{$title}] no se ha creado correctamente.", Constants::INTERNAL_SERVER_ERROR_CODE);
        } catch (InvalidArgumentException $e) {
            error_log("[SerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setErrorMessage($e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[SerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setWarningMessage($e->getMessage());
            return false;
        }
    }

    function edit($serieId, $serieData): bool
    {
        try {

            $this->checkValidIdDataType($serieId);
            $this->checkValidInputFields($serieData);

            $title = strtoupper($serieData[self::SERIE_TITLE]);
            $synopsis = strtoupper($serieData[self::SERIE_SYNOPSIS]);
            $releaseDate = $serieData[self::SERIE_RELEASE_DATE];

            $model = new Serie($serieId, $title, $synopsis, $releaseDate);
            $serieEdited = $model->update();

            if ($serieEdited) {
                $serieComponentsEdited = $this->updateSerieComponents($serieId, $serieData);

                if (!$serieComponentsEdited) {
                    error_log("[SerieController] [Dependency Error] Falló al actualizar en la tabla serie - id: [{$serieId}] title: [{$title}] synopsis: [{$synopsis}] release_date: [{$releaseDate}] platform ids: [" . implode(',', $serieData[self::SERIE_PLATFORMS]) . "] actor ids: [" . implode(',', $serieData[self::SERIE_ACTORS]) . "] director ids: [" . implode(',', $serieData[self::SERIE_DIRECTORS]) . "] audio language ids: [" . implode(',', $serieData[self::SERIE_AUDIO_LANGUAGES]) . "] subtitle language ids: [" . implode(',', $serieData[self::SERIE_SUBTITLE_LANGUAGES]) . "]");
                    return false;
                }

                Utilities::setSuccessMessage("Serie [{$title}] editada correctamente.");
                return true;
            }
        } catch (InvalidArgumentException $e) {
            error_log("[SerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setErrorMessage($e->getMessage());
            return false;
        }
    }

    function delete($serieId): bool
    {
        try {

            $this->checkValidIdDataType($serieId);

            $serieSaved = $this->showById($serieId);

            if (is_bool($serieSaved) && !$serieSaved) {
                return $serieSaved;
            }

            $model = new Serie($serieId);
            $serieDeleted = $model->delete();

            if (!$serieDeleted) {

                error_log("[SerieController] [Data Error] Falló al eliminar registro de la tabla serie - SERIE ID [{$serieId}]");
                throw new RuntimeException("La Serie [{$serieId}] no se ha eliminado correctamente.");
            }

            Utilities::setSuccessMessage("Serie [{$serieId}] eliminada correctamente.");
            return true;
        } catch (RecordNotFoundException $e) {
            error_log("[SerieController] [Record Not Found Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setWarningMessage($e->getMessage());
            return false;
        } catch (InvalidArgumentException $e) {
            error_log("[SerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setErrorMessage($e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[SerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            Utilities::setErrorMessage($e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el listado de series activas de una plataforma.
     * @param int $platformId ID de la plataforma.
     * @return string Listado de opciones de menu para las series de una plataforma.
     */
    function getSeriesListByPlatform($platformId): string
    {

        $options = '';
        $withoutOrInactiveRecords = '<li class="list-group-item">No se encontraron series</li>';

        $platformSerieController = new PlatformSerieController();

        $platformSerieList = $platformSerieController->showByPlatformId($platformId);

        if (is_bool($platformSerieList) && !$platformSerieList) {

            $options .= $withoutOrInactiveRecords;
        } else {
            foreach ($platformSerieList as $platformSerieItem) {
                if ($platformSerieItem->getStatus() == 1) {

                    $serie = $this->showById($platformSerieItem->getSerieId());
                    $seriesPlatformOption = Utilities::concatStrings("[", $serie->getId(), "]", " - ", $serie->getTitle());

                    $options .= '<li class="list-group-item">' . $seriesPlatformOption . '</li>';
                }
            }
            if ($options == '') {
                $options .= $withoutOrInactiveRecords;
            }
        }

        return $options;
    }

    /**
     * Obtiene el listado de series activas de un actor.
     * @param int $actorId ID del actor.
     * @return string Listado de opciones de menu para las series de un actor.
     */
    function getSeriesListByActor($actorId): string
    {

        $options = '';
        $withoutOrInactiveRecords = '<li class="list-group-item">No se encontraron series</li>';

        $actorSerieController = new ActorSerieController();

        $directorSerieList = $actorSerieController->showByActorId($actorId);

        if (is_bool($directorSerieList) && !$directorSerieList) {

            $options .= $withoutOrInactiveRecords;
        } else {
            foreach ($directorSerieList as $directorSerieItem) {
                if ($directorSerieItem->getStatus() == 1) {

                    $serie = $this->showById($directorSerieItem->getSerieId());
                    $seriesDirectorOption = Utilities::concatStrings("[", $serie->getId(), "]", " - ", $serie->getTitle());

                    $options .= '<li class="list-group-item">' . $seriesDirectorOption . '</li>';
                }
            }
            if ($options == '') {
                $options .= $withoutOrInactiveRecords;
            }
        }

        return $options;
    }

    /**
     * Obtiene el listado de series activas de un director.
     * @param int $directorId ID del director.
     * @return string Listado de opciones de menu para las series de un director.
     */
    function getSeriesListByDirector($directorId): string
    {

        $options = '';
        $withoutOrInactiveRecords = '<li class="list-group-item">No se encontraron series</li>';

        $directorSerieController = new DirectorSerieController();

        $directorSerieList = $directorSerieController->showByDirectorId($directorId);

        if (is_bool($directorSerieList) && !$directorSerieList) {

            $options .= $withoutOrInactiveRecords;
        } else {
            foreach ($directorSerieList as $directorSerieItem) {
                if ($directorSerieItem->getStatus() == 1) {

                    $serie = $this->showById($directorSerieItem->getSerieId());
                    $seriesDirectorOption = Utilities::concatStrings("[", $serie->getId(), "]", " - ", $serie->getTitle());

                    $options .= '<li class="list-group-item">' . $seriesDirectorOption . '</li>';
                }
            }
            if ($options == '') {
                $options .= $withoutOrInactiveRecords;
            }
        }

        return $options;
    }

    /**
     * Obtiene el listado de series activas de una idioma.
     * @param int $languageId ID del idioma.
     * @return string Listado de opciones de menu para las series de un idioma.
     */
    function getSeriesListByLanguage($languageId): string
    {

        $options = '';
        $withoutOrInactiveRecords = '<li class="list-group-item">No se encontraron series</li>';

        $languageSerieController = new LanguageSerieController();

        $languageSerieList = $languageSerieController->showByLanguageId($languageId);

        if (is_bool($languageSerieList) && !$languageSerieList) {

            $options .= $withoutOrInactiveRecords;
        } else {
            foreach ($languageSerieList as $languageSerieItem) {
                if ($languageSerieItem->getAudio() == 1 || $languageSerieItem->getSubtitle() == 1) {

                    $serie = $this->showById($languageSerieItem->getSerieId());
                    $seriesLanguageOption = Utilities::concatStrings("[", $serie->getId(), "]", " - ", $serie->getTitle());

                    $options .= '<li class="list-group-item">' . $seriesLanguageOption . '</li>';
                }
            }
            if ($options == '') {
                $options .= $withoutOrInactiveRecords;
            }
        }

        return $options;
    }

    /**
     * Construye un objeto Serie.
     * @param Serie $source Modelo Serie.
     * @return Serie generado a partir del modelo.
     */
    private function makeSerie(Serie $source): Serie
    {
        return new Serie(
            $source->getId(),
            ucfirst(Utilities::convertCharacters(0, $source->getTitle())),
            $source->getSynopsis(),
            $source->getReleaseDate()
        );
    }

    /**
     * Crea los componentes de una serie: plataformas, actores, directores, idiomas de audio y subtitulos.
     * 
     * @param int $serieId El ID de la serie.
     * @param array $serieData Los datos de la serie.
     * @return bool True si la creación fue exitosa, false si falló.
     */
    private function createSerieComponents(int $serieId, array $serieData): bool
    {

        $platformIds = $serieData[self::SERIE_PLATFORMS];
        $actorIds = $serieData[self::SERIE_ACTORS];
        $directorIds = $serieData[self::SERIE_DIRECTORS];
        $languages = $this->matchLanguages(null, $serieData[self::SERIE_AUDIO_LANGUAGES], $serieData[self::SERIE_SUBTITLE_LANGUAGES]);

        if (!$this->createPlatformSeries($serieId, $platformIds)) {
            error_log("[SerieController] [Data Error] Falló al crear las plataformas de la serie en la tabla platform_serie - SERIE ID: [{$serieId}]");
            return false;
        }

        if (!$this->createActorSeries($serieId, $actorIds)) {
            error_log("[SerieController] [Data Error] Falló al crear los actores/actrices de la serie en la tabla actor_serie - SERIE ID: [{$serieId}]");
            return false;
        }

        if (!$this->createDirectorSeries($serieId, $directorIds)) {
            error_log("[SerieController] [Data Error] Falló al crear los/as directores/as de la serie en la tabla director_serie - SERIE ID: [{$serieId}]");
            return false;
        }

        if (!$this->createLanguageSeries($serieId, $languages)) {
            error_log("[SerieController] [Data Error] Falló al crear los idiomas disponibles de la serie en la tabla language_serie - SERIE ID: [{$serieId}]");
            return false;
        }

        return true;
    }


    /**
     * Actualiza los componentes de una serie: plataformas, actores, directores, idiomas de audio y subtitulos.
     * 
     * @param int $serieId El ID de la serie.
     * @param array $serieData Los datos de la serie.
     * @return bool True si la actualización fue exitosa, false si falló.
     */
    private function updateSerieComponents(int $serieId, array $serieData): bool
    {
        $platformIds = $serieData[self::SERIE_PLATFORMS];
        $actorIds = $serieData[self::SERIE_ACTORS];
        $directorIds = $serieData[self::SERIE_DIRECTORS];
        $languages = $this->matchLanguages($serieId, $serieData[self::SERIE_AUDIO_LANGUAGES], $serieData[self::SERIE_SUBTITLE_LANGUAGES]);

        if (!$this->editPlatformSeries($serieId, $platformIds)) {
            error_log("[SerieController] [Data Error] Falló al actualizar las plataformas de la serie en la tabla platform_serie - SERIE ID: [{$serieId}]");
            return false;
        }

        if (!$this->editActorSeries($serieId, $actorIds)) {
            error_log("[SerieController] [Data Error] Falló al actualizar los actores/actrices de la serie en la tabla actor_serie - SERIE ID: [{$serieId}]");
            return false;
        }

        if (!$this->editDirectorSeries($serieId, $directorIds)) {
            error_log("[SerieController] [Data Error] Falló al actualizar los/as directores/as de la serie en la tabla director_serie - SERIE ID: [{$serieId}]");
            return false;
        }

        if (!$this->editLanguageSeries($serieId, $languages)) {
            error_log("[SerieController] [Data Error] Falló al actualizar los idiomas disponibles de la serie en la tabla language_serie - SERIE ID: [{$serieId}]");
            return false;
        }

        return true;
    }

    /**
     * Crea registros para las plataformas de una serie.
     * @param int $serieId EL ID de la serie.
     * @param array $platformIds IDs de las plataformas de la serie.
     * @return bool True si la creación fue exitosa, false si falló.
     */
    private function createPlatformSeries(int $serieId, array $platformIds): bool
    {
        $platformSerieController = new PlatformSerieController();
        return $platformSerieController->create($serieId, $platformIds);
    }

    /**
     * Edita registros para las plataformas de una serie.
     * @param int $serieId EL ID de la serie.
     * @param array $platformIds IDs de las plataformas de la serie.
     * @return bool True si la edición fue exitosa, false si falló.
     */
    private function editPlatformSeries(int $serieId, array $platformIds): bool
    {
        $platformSerieController = new PlatformSerieController();
        return $platformSerieController->edit($serieId, $platformIds);
    }

    /**
     * Crea registros para los actores/actrices de una serie.
     * @param int $serieId EL ID de la serie.
     * @param array $actorIds IDs de los actores/actrices de la serie.
     * @return bool True si la creación fue exitosa, false si falló.
     */
    private function createActorSeries(int $serieId, array $actorIds): bool
    {
        $actorSerieController = new ActorSerieController();
        return $actorSerieController->create($serieId, $actorIds);
    }

    /**
     * Edita registros para los actores/actrices de una serie.
     * @param int $serieId EL ID de la serie.
     * @param array $actorIds IDs de los actores/actrices de la serie.
     * @return bool True si la edición fue exitosa, false si falló.
     */
    private function editActorSeries(int $serieId, array $actorIds): bool
    {
        $actorSerieController = new ActorSerieController();
        return $actorSerieController->edit($serieId, $actorIds);
    }

    /**
     * Crea registros para los/as directores/as de una serie.
     * @param int $serieId EL ID de la serie.
     * @param array $directorIds IDs de los directores/as de la serie.
     * @return bool True si la creación fue exitosa, false si falló.
     */
    private function createDirectorSeries(int $serieId, array $directorIds): bool
    {
        $directorSerieController = new DirectorSerieController();
        return $directorSerieController->create($serieId, $directorIds);
    }

    /**
     * Edita registros para los/as directores/as de una serie.
     * @param int $serieId EL ID de la serie.
     * @param array $directorIds IDs de los/as directores/as de la serie.
     * @return bool True si la edición fue exitosa, false si falló.
     */
    private function editDirectorSeries(int $serieId, array $directorIds): bool
    {
        $directorSerieController = new DirectorSerieController();
        return $directorSerieController->edit($serieId, $directorIds);
    }

    /**
     * Crea registros para los idiomas de una serie.
     * @param int $serieId EL ID de la serie.
     * @param array $languages Idiomas de la serie.
     * @return bool True si la creación fue exitosa, false si falló.
     */
    private function createLanguageSeries(int $serieId, array $languages): bool
    {
        $languageSerieController = new LanguageSerieController();
        return $languageSerieController->create($serieId, $languages);
    }

    /**
     * Edita registros para los idiomas de una serie.
     * @param int $serieId EL ID de la serie.
     * @param array $languages Idiomas de la serie.
     * @return bool True si la edición fue exitosa, false si falló.
     */
    private function editLanguageSeries(int $serieId, array $languages): bool
    {
        $languageSerieController = new LanguageSerieController();
        return $languageSerieController->edit($serieId, $languages);
    }

    /**
     * Valida el tipo de dato del ID de la serie.
     * @param int $id EL ID de la serie.
     * @throws InvalidArgumentException si no cumple con el formato establecido.
     */
    private function checkValidIdDataType($id): void
    {
        if (SerieValidation::isInvalidIdDataType($id)) {
            error_log("[SerieController] [Validation Error] ID de la serie inválido. Debe contener solo números - [{$id}]");
            throw new InvalidArgumentException("Id [{$id}] de la serie inválido", Constants::BAD_REQUEST_CODE);
        }
    }

    /**
     * Valida el tipo de dato de los componentes de una serie.
     * @param array $serieData Los datos de la serie.
     * @throws InvalidArgumentException si no cumple con el formato establecido.
     */
    private function checkValidInputFields($serieData): void
    {
        $inputInvalid = false;

        $title = $serieData[self::SERIE_TITLE];
        if (CommonValidation::hasInvalidLength($title, 50)) {
            error_log("[SerieController] [Validation Error] Titulo no enviado, no es una cadena válida o excede el límite de 50 carácteres alfabéticos- [{$title}]");
            $inputInvalid = true;
        }

        $synopsis = $serieData[self::SERIE_SYNOPSIS];
        if (CommonValidation::hasInvalidLength($synopsis, Constants::SYNOPSIS_LENGTH)) {
            error_log("[SerieController] [Validation Error] Sinopsis no enviada, no es una cadena válida o excede el límite de " . Constants::SYNOPSIS_LENGTH . " carácteres alfabéticos- [{$synopsis}]");
            $inputInvalid = true;
        }

        $releaseDate = $serieData[self::SERIE_RELEASE_DATE];
        if (empty($releaseDate) || CommonValidation::isInvalidDate($releaseDate)) {
            error_log("[SerieController] [Validation Error] Fecha de lanzamiento no enviada o no cumple con un formato de fecha aceptado - [{$releaseDate}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            throw new InvalidArgumentException("Por favor verificar la información ingresada.", Constants::BAD_REQUEST_CODE);
        }
    }

    private function matchLanguages($serieId, array $audioLanguages, array $subtitleLanguages): array
    {
        $languages = [];

        // Intersección de arrays - ambos componentes
        $bothLanguages = array_intersect($audioLanguages, $subtitleLanguages);
        $languages = array_fill_keys($bothLanguages, ['audio' => 1, 'subtitle' => 1]);

        // Diferencias de arrays - solo audio
        $onlyAudioLanguage = array_diff($audioLanguages, $subtitleLanguages);
        $languages += array_fill_keys($onlyAudioLanguage, ['audio' => 1, 'subtitle' => 0]);

        // Diferencias de arrays - solo subtítulos
        $onlySubtitleLanguage = array_diff($subtitleLanguages, $audioLanguages);
        $languages += array_fill_keys($onlySubtitleLanguage, ['audio' => 0, 'subtitle' => 1]);

        // Detectar idiomas que han sido removidos de ambos arrays
        if ($serieId != null) {
            $languageSerieController = new LanguageSerieController();

            $savedSerieLanguages = $languageSerieController->showBySerieId($serieId);
            $savedIdsSerieLanguages = [];
            foreach ($savedSerieLanguages as $savedSerieLanguageItem) {
                $savedIdsSerieLanguages[] = $savedSerieLanguageItem->getLanguageId();
            }

            foreach ($savedIdsSerieLanguages as $languageId) {
                if (!in_array($languageId, $audioLanguages) && !in_array($languageId, $subtitleLanguages)) {
                    $languages[$languageId] = ['audio' => 0, 'subtitle' => 0];
                }
            }
        }

        return $languages;
    }
}

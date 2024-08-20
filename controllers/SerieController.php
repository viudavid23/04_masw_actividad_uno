<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/Serie.php');
require_once('exceptions/RecordNotFoundException.php');
require_once('validations/CommonValidation.php');
require_once('validations/SerieValidation.php');
require_once('PlatformSerieController.php');
require_once('ActorSerieController.php');
class SerieController
{
    const SERIE_TITLE = 'title';
    const SERIE_SYNOPSIS = 'synopsis';
    const SERIE_RELEASE_DATE = 'release_date';
    const SERIE_PLATFORMS = 'platforms';
    const SERIE_ACTORS = 'actors';

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
            $platformIds = $serieData[self::SERIE_PLATFORMS];
            $actorIds = $serieData[self::SERIE_ACTORS];

            $serieModel = new Serie(null, $title, $synopsis, $releaseDate);
            $serieSaved = $serieModel->save();

            if ($serieSaved->getId() !== null) {

                $serieId = $serieSaved->getId();

                if ($this->createPlatformSeries($serieId, $platformIds) && $this->createActorSeries($serieId, $actorIds)) {

                    Utilities::setSuccessMessage("Serie [{$title}] creada correctamente.");
                    return true;
                } else {

                    $serieModel->delete($serieId);

                    $platformIdsEncode = json_encode($platformIds);
                    error_log("[SerieController] [Dependency Error] Falló al guardar la serie - Titulo: [{$title}] Sinopsis: [{$synopsis}] Fecha Lanzamiento: [{$releaseDate}] Causado por las Plataformas: [{$platformIdsEncode}]");
                    throw new RuntimeException("La Serie [{$title}] no se ha creado correctamente.", Constants::FAILED_DEPENDENCY_CODE);
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

    function edit($id, $serieData): bool
    {
        try {

            $this->checkValidIdDataType($id);
            $this->checkValidInputFields($serieData);

            $title = strtoupper($serieData[self::SERIE_TITLE]);
            $synopsis = strtoupper($serieData[self::SERIE_SYNOPSIS]);
            $releaseDate = $serieData[self::SERIE_RELEASE_DATE];

            $model = new Serie($id, $title, $synopsis, $releaseDate);
            $serieEdited = $model->update();

            if ($serieEdited) {
                $serieComponentsEdited = $this->updateSerieComponents($id, $serieData);

                if (!$serieComponentsEdited) {
                    error_log("[SerieController] [Data Error] Falló al actualizar la tabla serie - Id: [{$id}] Titulo: [{$title}] Sinopsis: [{$synopsis}] Fecha Lanzamiento: [{$releaseDate}] Plataformas: " . implode(',', $serieData[self::SERIE_PLATFORMS]) . " Actores/Actrices: " . implode(',', $serieData[self::SERIE_ACTORS]));
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

    function delete($id): bool
    {
        try {

            $this->checkValidIdDataType($id);

            $this->showById($id);

            $model = new Serie($id);
            $serieDeleted = $model->delete();

            if ($serieDeleted) {

                Utilities::setSuccessMessage("Serie [{$id}] eliminada correctamente.");
                return true;
            }

            error_log("[SerieController] [Data Error] Falló al eliminar registro de la tabla serie - ID [{$id}]");
            throw new RuntimeException("La Serie [{$id}] no se ha eliminado correctamente.");
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

        if (!$this->editPlatformSeries($serieId, $platformIds)) {
            error_log("[SerieController] [Data Error] Falló al actualizar las plataformas de la serie - Id: [{$serieId}]");
            return false;
        }

        if (!$this->editActorSeries($serieId, $actorIds)) {
            error_log("[SerieController] [Data Error] Falló al actualizar los actores/actrices de la serie - Id: [{$serieId}]");
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
}

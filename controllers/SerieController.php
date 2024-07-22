<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/Serie.php');
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

        return $serieModelList;
    }

    function showById($id): mixed
    {
        try {
            $this->checkValidIdDataType($id);

            $model = new Serie($id);
            $serieSaved = $model->getById();

            if (!$serieSaved) {
                error_log("[SerieController] [Data Error] ID de la serie no encontrado en la base de datos - [{$id}]");
                throw new RuntimeException("Serie [{$id}] no registrada", Constants::NOT_FOUND_CODE);
            }

            return $this->makeSerie($serieSaved);
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

            error_log("[SerieController] [Data Error] Falló al guardar la serie - Titulo: [{$title}] Sinopsis: [{$synopsis}] Fecha Lanzamiento: [{$releaseDate}]");
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

                $platformIds = $serieData[self::SERIE_PLATFORMS];

                if ($this->editPlatformSeries($id, $platformIds)) {
                    Utilities::setSuccessMessage("Serie [{$title}] editada correctamente.");
                    return true;
                }
            }

            error_log("[SerieController] [Data Error] Falló al actualizar la serie - Id: [{$id}]  Titulo: [{$title}] Sinopsis: [{$synopsis}] Fecha Lanzamiento: [{$releaseDate}] Plataformas: [{$platformIds}]");
            throw new RuntimeException("La Serie [{$title}] no se ha editado correctamente.");
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

            error_log("[SerieController] [Data Error] Falló al eliminar la Serie - ID [{$id}]");
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

    private function makeSerie(Serie $source): Serie
    {
        return new Serie(
            $source->getId(),
            ucfirst(Utilities::convertCharacters(0, $source->getTitle())),
            $source->getSynopsis(),
            $source->getReleaseDate()
        );
    }

    private function checkValidIdDataType($id): void
    {
        if (SerieValidation::isInvalidIdDataType($id)) {
            error_log("[SerieController] [Validation Error] ID de la serie inválido. Debe contener solo números - [{$id}]");
            throw new InvalidArgumentException("Id [{$id}] de la serie inválido", Constants::BAD_REQUEST_CODE);
        }
    }

    private function createPlatformSeries(int $serieId, array $platformIds): bool
    {
        $platformSerieController = new PlatformSerieController();
        return $platformSerieController->create($serieId, $platformIds);
    }

    private function editPlatformSeries(int $serieId, array $platformIds): bool
    {
        $platformSerieController = new PlatformSerieController();
        return $platformSerieController->edit($serieId, $platformIds);
    }

    private function createActorSeries(int $serieId, array $actorIds): bool
    {
        $actorSerieController = new ActorSerieController();
        return $actorSerieController->create($serieId, $actorIds);
    }


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

        $platformSerieData = $serieData[self::SERIE_PLATFORMS];
        if (CommonValidation::isInvalidIntegerList($platformSerieData)) {
            $platformIdsEncode = json_encode($platformSerieData);
            error_log("[SerieController] [Validation Error] Listado de plataformas no enviado o no contiene solo números enteros positivos - [{$platformIdsEncode}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            throw new InvalidArgumentException("Por favor verificar la información ingresada.", Constants::BAD_REQUEST_CODE);
        }
    }
}

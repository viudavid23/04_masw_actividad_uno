<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('exceptions/RecordNotFoundException.php');
require_once('validations/SerieValidation.php');
require_once('../../models/DirectorSerie.php');
require_once('DirectorController.php');
class DirectorSerieController
{

    function showBySerieId($serieId): mixed
    {
        try {
            $this->checkValidSerieIdDataType($serieId);

            $model = new DirectorSerie(null, $serieId);

            $directorSerieSaved = $model->getBySerieId();

            if (count($directorSerieSaved) == 0) {
                error_log("[DirectorSerieController] [Data Error] ID de la serie no encontrado en la tabla director_serie de base de datos - [{$serieId}]");
                return false;
            }

            return $directorSerieSaved;
        } catch (InvalidArgumentException $e) {
            error_log("[DirectorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function showByDirectorId($directorId): mixed
    {
        try {
            $this->checkValidSerieIdDataType($directorId);

            $model = new DirectorSerie($directorId, null);

            $directorSerieSaved = $model->getByDirectorId();

            if (count($directorSerieSaved) == 0) {
                error_log("[DirectorSerieController] [Data Error] ID del director/a no encontrado en la tabla director_serie de base de datos - [{$directorId}]");
                return false;
            }

            return $directorSerieSaved;
        } catch (InvalidArgumentException $e) {
            error_log("[DirectorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function create($serieId, $directorIdsData): bool
    {
        try {
            $this->checkValidSerieIdDataType($serieId);
            $this->checkValidDirectorSerieInputFields($directorIdsData);
            
            $model = new DirectorSerie(null, $serieId);
            $directorSerieSaved = $model->save($directorIdsData);

            if ($directorSerieSaved) {
                return true;
            }

            $directorIdsDecode = json_encode($directorIdsData);
            error_log("[DirectorSerieController] [Data Error] Falló al guardar los directores/as [{$directorIdsDecode}] de la serie [{$serieId}] en la tabla director_serie");
            throw new RuntimeException("Los/as directores/as [{$directorIdsDecode}] de la serie [{$serieId}] no se han creado correctamente.", Constants::INTERNAL_SERVER_ERROR_CODE);
        } catch (RecordNotFoundException $e) {
            error_log("[DirectorSerieController] [Record Not Found Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (InvalidArgumentException $e) {
            error_log("[DirectorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[DirectorSerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function edit($serieId, $newDirectorIds): bool
    {
        try {
            $this->checkValidSerieIdDataType($serieId);
            $this->checkValidDirectorSerieInputFields($newDirectorIds);
            $this->checkValidDirector($newDirectorIds);

            $directorSerieSaved = $this->showBySerieId($serieId);
            $savedDirectorIds = [];

            foreach ($directorSerieSaved as $item) {
                $savedDirectorIds[] = $item->getDirectorId();
            }

            $this->saveNewSerieActors($serieId, $newDirectorIds, $savedDirectorIds);

            $directorSerieToUpdate = Utilities::setArrayToUpdate($newDirectorIds, $savedDirectorIds);

            $model = new DirectorSerie(null, $serieId);

            $directorSerieEdited = $model->update($directorSerieToUpdate);

            if ($directorSerieEdited) {
                return true;
            }

            $directorIdsDecode = json_encode($newDirectorIds);
            error_log("[DirectorSerieController] [Data Error] Falló al actualizar los directores/as [{$directorIdsDecode}] de la serie [{$serieId}] en la tabla director_serie");
            throw new RuntimeException("Los/as directores/as [{$directorIdsDecode}] de la serie [{$serieId}] no se han editado correctamente.", Constants::INTERNAL_SERVER_ERROR_CODE);
        } catch (RecordNotFoundException $e) {
            error_log("[DirectorSerieController] [Record Not Found Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (InvalidArgumentException $e) {
            error_log("[DirectorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[DirectorSerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    private function saveNewSerieActors(int $serieId, array $newDirectorIds, array $savedDirectorIds): void
    {
        $newDirectorSerie = array_diff($newDirectorIds, $savedDirectorIds);

        if (!empty($newDirectorSerie)) {
            $this->create($serieId, $newDirectorSerie);
        }
    }

    private function checkValidDirectorSerieInputFields($directorIdsData): void
    {
        $inputInvalid = false;

        if (CommonValidation::isInvalidIntegerList($directorIdsData)) {
            $directorIdsEncode = json_encode($directorIdsData);
            error_log("[DirectorSerieController] [Validation Error] Listado de directores/as de la serie no enviado o no contiene solo números enteros positivos - [{$directorIdsEncode}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            throw new InvalidArgumentException("Por favor verificar la información ingresada.", Constants::BAD_REQUEST_CODE);
        }
    }

    private function checkValidSerieIdDataType($id): void
    {
        if (SerieValidation::isInvalidIdDataType($id)) {
            error_log("[DirectorSerieController] [Validation Error] ID de la serie inválido. Debe contener solo números - [{$id}]");
            throw new InvalidArgumentException("Id [{$id}] de la serie inválido", Constants::BAD_REQUEST_CODE);
        }
    }

    private function checkValidDirector($directorIdsData)
    {
        $directorController = new DirectorController();
        foreach ($directorIdsData as $directorItem) {
            $directorSaved = $directorController->showDirectorById($directorItem);
            if (!$directorSaved) {
                error_log("[DirectorSerieController] [Data Error] ID del/la director/a no encontrado en la base de datos - [{$directorItem}]");
                throw new RecordNotFoundException("Director/a [{$directorItem}] no registrado/a", Constants::NOT_FOUND_CODE);
            }
        }
    }

    function getDirectorSerieOptions(int $serieId): string
    {
        $directorController = new DirectorController();

        $directorSerieList = $this->showBySerieId($serieId);

        $directorList = $directorController->showAllDirectors();

        $selectedDirectorIds = [];
        if (isset($directorSerieList) && !empty($directorSerieList)) {
            foreach ($directorSerieList as $directorSerieItem) {
                if ($directorSerieItem->getStatus() == 1) {
                    $selectedDirectorIds[] = $directorSerieItem->getDirectorId();
                }
            }
        }

        $options = '';

        foreach ($directorList as $itemDirector) {
            $directorId = $itemDirector->getId();
            $person = $directorController->showPersonById($itemDirector->getPersonId());
            $directorOption = Utilities::concatStrings("[", $directorId, "]", " - ", $person->getFirstName(), " ", $person->getLastName());

            if (in_array($directorId, $selectedDirectorIds)) {
                $options .= '<option value="' . $directorId . '" selected=' . $directorId . '>' . $directorOption . '</option>';
            } else {
                $options .= '<option value="' . $directorId . '">' . $directorOption . '</option>';
            }
        }

        return $options;
    }

    function checkActiveDirectorSeries($directorId): bool
    {
        $hasActiveSeries = false;

        $directorSerieList = $this->showByDirectorId($directorId);

        if (isset($directorSerieList) && !empty($directorSerieList)) {

            foreach ($directorSerieList as $directorSerieItem) {
                if ($directorSerieItem->getStatus() == 1) {
                    error_log("No se puede eliminar el/la director/a de la tabla director, tiene series activas en la tabla director_serie - DIRECTOR_ID: {$directorId}");
                    Utilities::setWarningMessage("El director/a tiene series asociadas");
                    $hasActiveSeries = true;
                    break;
                }
            }
        }

        return $hasActiveSeries;
    }
}

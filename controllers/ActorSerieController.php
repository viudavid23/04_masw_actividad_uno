<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/ActorSerie.php');
require_once('ActorController.php');
class ActorSerieController
{

    function showBySerieId($serieId): mixed
    {
        try {
            $this->checkValidSerieIdDataType($serieId);

            $model = new ActorSerie(null, $serieId);

            $actorSerieSaved = $model->getBySerieId();

            if (count($actorSerieSaved) == 0) {
                error_log("[ActorSerieController] [Data Error] ID de la serie no encontrado en la base de datos - [{$serieId}]");
                Utilities::setWarningMessage("Serie [{$serieId}] no registrada", Constants::NOT_FOUND_CODE);
                return false;
            }

            return $actorSerieSaved;
        } catch (InvalidArgumentException $e) {
            error_log("[ActorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function create($serieId, $actorIdsData): bool
    { 
        try {
            $this->checkValidSerieIdDataType($serieId);
            $this->checkValidActorSerieInputFields($actorIdsData);
            $this->checkValidActor($actorIdsData);

            $model = new ActorSerie(null, $serieId);
            $actorSerieSaved = $model->save($actorIdsData);

            if ($actorSerieSaved) {
                return true;
            }

            $actorIdsDecode = json_encode($actorIdsData);
            error_log("[ActorSerieController] [Data Error] Falló al guardar los actores/actrices [{$actorIdsDecode}] de la serie [{$serieId}]");
            throw new RuntimeException("Los/as actores/actrices [{$actorIdsDecode}] de la serie [{$serieId}] no se han creado correctamente.", Constants::INTERNAL_SERVER_ERROR_CODE);
        } catch (InvalidArgumentException $e) {
            error_log("[ActorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[ActorSerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function edit($serieId, $newActorIds): bool
    {
        $actorSerieSaved = $this->showBySerieId($serieId);
        $actorSerieIds = [];

        foreach($actorSerieSaved as $item){
            $actorSerieIds[] = $item->getActorId();
        }   

        $this->saveNewSerieActors($serieId, $newActorIds, $actorSerieIds);

        $actorSerieToUpdate = Utilities::setArrayToUpdate($newActorIds, $actorSerieIds);

        $model = new ActorSerie(null, $serieId);

        $actorSerieEdited = $model->update($actorSerieToUpdate);

        return $actorSerieEdited;
    }

    private function setSerieActorToUpdate(array $newActorIds, array $actorSerieIdsSaved): array
    {
        $actorSerieToUpdate = [];
        foreach ($actorSerieIdsSaved as $currentActorSerieItem) {

            $actorSerieToUpdate[$currentActorSerieItem] = in_array($currentActorSerieItem, $newActorIds) ? 1 : 0;
        }

        return $actorSerieToUpdate;
    }

    private function saveNewSerieActors(int $serieId, array $newActorIds, array $actorSerieIdsSaved): void
    {
        $newActorSerie = array_diff($newActorIds, $actorSerieIdsSaved);

        if (!empty($newActorSerie)) {
            $this->create($serieId, $newActorSerie);
        }
    }

    function delete($id): bool
    {
        $serieDeleted = false;

        if (!$this->checkValidSerieIdDataType($id)) {
            return $serieDeleted;
        }

        $model = new Serie($id);

        $serieSaved = $model->getById();

        if (!$serieSaved) {
            error_log("[ActorSerieController] [Data Error] Serie no encontrada en la base de datos - ID [{$id}]");
            return $serieDeleted;
        }

        $serieDeleted = $model->delete();

        if (!$serieDeleted) {
            error_log("[ActorSerieController] [Data Error] Falló al eliminar la Serie - ID [{$id}]");
        }

        return $serieDeleted;
    }


    private function checkValidActorSerieInputFields($actorIdsData): void
    {
        $inputInvalid = false;

        if (CommonValidation::isInvalidIntegerList($actorIdsData)) {
            $actorIdsEncode = json_encode($actorIdsData);
            error_log("[ActorSerieController] [Validation Error] Listado de actores/actrices de la serie no enviado o no contiene solo números enteros positivos - [{$actorIdsEncode}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            throw new InvalidArgumentException("Por favor verificar la información ingresada.", Constants::BAD_REQUEST_CODE);
        }
    }

    private function checkValidSerieIdDataType($id): void
    {
        if (SerieValidation::isInvalidIdDataType($id)) {
            error_log("[ActorSerieController] [Validation Error] ID de la serie inválido. Debe contener solo números - [{$id}]");
            throw new InvalidArgumentException("Id [{$id}] de la serie inválido", Constants::BAD_REQUEST_CODE);
        }
    }

    private function checkValidActor($actorIdsData)
    {
        $actorController = new ActorController();
        foreach ($actorIdsData as $actorItem) {
            $platformSaved = $actorController->showActorById($actorItem);
            if (!$platformSaved) {
                error_log("[ActorSerieController] [Data Error] ID del/la actor/actriz no encontrado en la base de datos - [{$actorItem}]");
                throw new RuntimeException("Actor [{$actorItem}] no registrado", Constants::NOT_FOUND_CODE);
            }
        }
    }

    function getActorSerieOptions(int $serieId): string
    {
        $actorController = new ActorController();
        
        $actorSerieList = $this->showBySerieId($serieId);

        $actorList = $actorController->showAllActors();
        
        $selectedActorIds = [];
        if (isset($actorSerieList) && !empty($actorSerieList)) {
            foreach ($actorSerieList as $actorSerieItem) {
                if ($actorSerieItem->getStatus() == 1) {
                    $selectedActorIds[] = $actorSerieItem->getActorId();
                }   
            }
        }

        $options = '';

        foreach ($actorList as $itemActor) {
            $actorId = $itemActor->getId();
            $person = $actorController->showPersonById($itemActor->getPersonId());
            $actorOption = Utilities::concatStrings("[", $actorId, "]", " - " ,$person->getFirstName() , " ", $person->getLastName());

            if (in_array($actorId, $selectedActorIds)) {
                $options .= '<option value="' . $actorId . '" selected=' . $actorId . '>' . $actorOption . '</option>';
            } else {
                $options .= '<option value="' . $actorId . '">' . $actorOption . '</option>';
            }
        }

        return $options;
    }
}

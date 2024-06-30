<?php
require_once '../../utils/SessionStart.php';
require_once('../../models/Actor.php');
require_once '../../utils/Utilities.php';
require_once('validations/ActorValidation.php');
require_once('PersonController.php');

class ActorController extends PersonController
{

    function showAllActors(): array
    {
        $model = new Actor();
        $actorList = $model->getAll();
        $actorObjectArray = [];

        foreach ($actorList as $item) {
            $actorObject = new Actor(
                $item->getId(),
                ucfirst(Utilities::convertCharacters(0, $item->getStageName())),
                Utilities::convertCharacters(0, $item->getBiography()),
                Utilities::convertCharacters(0, $item->getAwards()),
                $item->getHeight(),
                $item->getPersonId()
            );
            array_push($actorObjectArray, $actorObject);
        }
        return $actorObjectArray;
    }

    function showActorById($id): mixed
    {
        if (!$this->hasValidActorIdType($id)) {
            return false;
        }

        $model = new Actor($id);
        $actorSaved = $model->getById();

        if (!$actorSaved) {
            $_SESSION['error_message'] = "Actor no encontrado";
            error_log("Database exception: ID del actor no encontrado en la base de datos - [{$id}]");
            return false;
        }

        $actor = new Actor(
            $actorSaved->getId(),
            ucfirst(Utilities::convertCharacters(0, $actorSaved->getStageName())),
            Utilities::convertCharacters(0, $actorSaved->getBiography()),
            Utilities::convertCharacters(0, $actorSaved->getAwards()),
            $actorSaved->getHeight(),
            $actorSaved->getPersonId()
        );

        return $actor;
    }

    function createActor(array $actorData, array $personData): bool
    {
        $actorSaved = false;

        if ($this->hasInvalidPersonInputFields($personData) || $this->hasInvalidActorInputFields($actorData, false)) {
            return $actorSaved;
        }

        $personSaved = $this->createPerson($personData);

        if ($personSaved) {

            $stageName = $actorData['stageName'];
            $biography = $actorData['biography'];
            $awards = $actorData['awards'];
            $height = $actorData['height'];
            $personId = $personSaved->getId();
    
            $model = new Actor(null, strtoupper($stageName), $biography, $awards, $height, $personId);
    
            $actorSaved = $model->save();
    
            if ($actorSaved) {
    
                $_SESSION['success_message'] = 'La información del Actor se ha registrado correctamente.';
            }else{

                $this->deletePerson($personId);

                $_SESSION['error_message'] = 'La información del Actor no se ha registrado correctamente.';
                error_log("Database exception: Fallo al guardar el actor - Nombre Artistico [{$stageName}] ) Altura [{$height}] ID Persona [{$personId}]");
            }
        }
        return $actorSaved;
    }

    function editActor($actorId, array $actorData, array $personData): mixed
    {
        $actorEdited = false;

        $personId = $personData['id'];

        if (!$this->hasValidActorIdType($actorId) || !$this->hasValidPersonIdType($personId) || $this->hasInvalidActorInputFields($actorData, true) || $this->hasInvalidPersonInputFields($personData)) {
            return $actorEdited;
        }

        $stageName = $actorData['stageName'];
        $biography = $actorData['biography'];
        $awards = $actorData['awards'];
        $height = $actorData['height'];

        $model = new Actor($actorId, strtoupper($stageName), $biography, $awards, $height, $personId);

        $actorEdited = $model->update();

        if ($actorEdited) {

            $personEdited = $this->editPerson($personId, $personData);

            $this->handlePersonEditedMessage($personEdited);
            
        }else{
            $_SESSION['error_message'] = 'Actor no se ha actualizado correctamente.';
            error_log("Database exception: Fallo al editar el actor - Id: [{$actorId}] Nombre Artistico [{$stageName}] ) Altura [{$height}] ID Persona [{$personId}]");
        }

        return $actorEdited;
    }

    function deleteActor($id): bool
    {
        $actorDeleted = false;

        if (!$this->hasValidActorIdType($id)) {
            return $actorDeleted;
        }

        $model = new Actor($id);

        $actorDeleted = $model->getById();

        if (!$actorDeleted) {
            $_SESSION['warning_message'] = "Actor no encontrado";
            error_log("Database exception: ID del actor no encontrada en la base de datos - [{$id}]");
            return $actorDeleted;
        }

        $actorDeleted = $this->deletePerson($actorDeleted->getPersonId());

        if ($actorDeleted) {
            $_SESSION['success_message'] = 'La información del Actor se ha eliminado correctamente.';
        }else {
            $_SESSION['error_message'] = 'La información del Actor no se ha eliminado correctamente.';
            error_log("Database exception: Fallo al eliminar el actor - ID [{$id}]");
        }

        return $actorDeleted;
    }

    private function handlePersonEditedMessage(bool $personEdited){
        if ($personEdited) {
            $_SESSION['success_message'] = 'Actor y Datos Personales se han actulizado correctamente';
        } else {
            $_SESSION['error_message'] = 'Actor actualizado, pero los datos personales no se han actualizado correctamente.';
        }
    }

    private function hasValidActorIdType($id): bool
    {
        if (PersonValidation::validateIdDataType($id)) {
            error_log("Validation exception: ID del actor inválido. Debe contener solo números y ser mayor a cero - [{$id}]");
            return false;
        }
        return true;
    }

    private function hasInvalidActorInputFields($actorData, $edit): bool
    {
        $inputInvalid = false;

        if (ActorValidation::validateStageNameType($actorData['stageName'])) {
            error_log("Validation exception: Nombre Artistico no es una cadena válida de carácteres alfabéticos - [{$actorData['stageName']}]");
            $inputInvalid = true;
        }

        if (ActorValidation::validateLength($actorData['biography'])) {
            error_log("Validation exception: Biografía vacia o supera la cantidad maxima de caracteres de 5000. Longitud - [{" . strlen($actorData['biography']) . "}]");
            $inputInvalid = true;
        }

        if (ActorValidation::validateHeightDataType($actorData['height'])) {
            error_log("Validation exception: Altura debe contener solo números y ser mayor a cero - [{$actorData['height']}]");
            $inputInvalid = true;
        }

        if ($edit && ActorValidation::validateIdDataType($actorData['personId'])) {
            error_log("Validation exception: ID de la persona inválido. Debe contener solo números y ser mayor a cero  - [{$actorData['personId']}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            $_SESSION['error_message'] = "Por favor verificar la información ingresada.";
        }

        return $inputInvalid;
    }
}

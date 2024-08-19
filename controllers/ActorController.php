<?php
require_once '../../utils/SessionStart.php';
require_once('../../models/Actor.php');
require_once '../../utils/Utilities.php';
require_once('validations/ActorValidation.php');
require_once('validations/CommonValidation.php');
require_once('PersonController.php');

class ActorController extends PersonController
{

    function showAllActors(): array
    {
        $model = new Actor();
        $actorList = $model->getAll();
        $actorObjectArray = [];

        foreach ($actorList as $item) {
            $actorObject = $this->makeActor($item);
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
            $_SESSION['warning_message'] = "Actor/Actriz [{$id}] no registrado/a";
            error_log("Database exception: ID del actor/actriz no encontrado en la base de datos - [{$id}]");
            return false;
        }

        $actor = $this->makeActor($actorSaved);

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
    
                $_SESSION['success_message'] = 'La información del Actor/Actriz se ha registrado correctamente.';
            }else{

                $this->deletePerson($personId);

                $_SESSION['error_message'] = 'La información del Actor/Actriz no se ha registrado correctamente.';
                error_log("Database exception: Falló al guardar el actor - Nombre Artístico [{$stageName}] Altura [{$height}] ID Persona [{$personId}]");
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
            $_SESSION['error_message'] = 'Actor/Actriz no se ha actualizado correctamente.';
            error_log("Database exception: Falló al editar el/la actor/actriz - Id: [{$actorId}] Nombre Artístico [{$stageName}] Altura [{$height}] ID Persona [{$personId}]");
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
            $_SESSION['warning_message'] = "Actor/Actriz [{$id}] no registrado";
            error_log("Database exception: ID del actor/actriz no encontrada en la base de datos - [{$id}]");
            return $actorDeleted;
        }

        $actorDeleted = $this->deletePerson($actorDeleted->getPersonId());

        if ($actorDeleted) {
            $_SESSION['success_message'] = 'La información del Actor/Actriz se ha eliminado correctamente.';
        }else {
            $_SESSION['error_message'] = 'La información del Actor/Actriz no se ha eliminado correctamente.';
            error_log("Database exception: Falló al eliminar el/la actor/actriz - ID Actor/Actriz [{$id}]");
        }

        return $actorDeleted;
    }

    private function makeActor(Actor $source): Actor
    {
       return new Actor(
            $source->getId(),
            ucfirst(Utilities::convertCharacters(0, $source->getStageName())),
            Utilities::convertCharacters(0, $source->getBiography()),
            Utilities::convertCharacters(0, $source->getAwards()),
            $source->getHeight(),
            $source->getPersonId()
       );
    }

    private function handlePersonEditedMessage(bool $personEdited){
        if ($personEdited) {
            $_SESSION['success_message'] = 'Actor/Actriz y Datos Personales se han actualizado correctamente';
        } else {
            $_SESSION['error_message'] = 'Actor/Actriz actualizado, pero los datos personales no se han actualizado correctamente.';
        }
    }

    private function hasValidActorIdType($id): bool
    {
        if (ActorValidation::isInvalidIdDataType($id)) {
            $_SESSION['error_message'] = "Actor/Actriz [{$id}] inválido";
            error_log("Validation exception: ID del actor/actriz inválido. Debe contener solo números y ser mayor a cero - [{$id}]");
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

        if (CommonValidation::hasInvalidLength($actorData['biography'], Constants::BIOGRAPHY_LENGTH)) {
            error_log("Validation exception: Biografía vacia o supera la cantidad maxima de caracteres de " . Constants::BIOGRAPHY_LENGTH . " Longitud - [{" . strlen($actorData['biography']) . "}]");
            $inputInvalid = true;
        }

        if (ActorValidation::validateHeightDataType($actorData['height'])) {
            error_log("Validation exception: Altura debe contener solo números y ser mayor a cero - [{$actorData['height']}]");
            $inputInvalid = true;
        }

        if ($edit && PersonValidation::isInvalidIdDataType($actorData['personId'])) {
            error_log("Validation exception: ID de la persona inválido. Debe contener solo números y ser mayor a cero  - [{$actorData['personId']}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            $_SESSION['error_message'] = "Por favor verificar la información ingresada.";
        }

        return $inputInvalid;
    }
}

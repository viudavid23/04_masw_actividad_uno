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
            error_log("Database exception: ID del actor/actriz no encontrado en la base de datos - [{$id}]");
            Utilities::setWarningMessage("Actor/Actriz [{$id}] no registrado/a");
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
    
                Utilities::setSuccessMessage("La información del Actor/Actriz se ha registrado correctamente.");
            }else{

                $this->deletePerson($personId);

                error_log("Database exception: Falló al guardar el actor - Nombre Artístico [{$stageName}] Altura [{$height}] ID Persona [{$personId}]");
                Utilities::setErrorMessage("La información del Actor/Actriz no se ha registrado correctamente.");
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
            error_log("Database exception: Falló al editar el/la actor/actriz - Id: [{$actorId}] Nombre Artístico [{$stageName}] Altura [{$height}] ID Persona [{$personId}]");
            Utilities::setErrorMessage("Actor/Actriz no se ha actualizado correctamente.");
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
            error_log("Database exception: ID del actor/actriz no encontrada en la base de datos - [{$id}]");
            Utilities::setWarningMessage("Actor/Actriz [{$id}] no registrado");
            return $actorDeleted;
        }

        $actorDeleted = $this->deletePerson($actorDeleted->getPersonId());

        if ($actorDeleted) {
            Utilities::setSuccessMessage("La información del Actor/Actriz se ha eliminado correctamente.");
        }else {
            error_log("Database exception: Falló al eliminar el/la actor/actriz - ID Actor/Actriz [{$id}]");
            Utilities::setErrorMessage("La información del Actor/Actriz no se ha eliminado correctamente.");
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
            Utilities::setSuccessMessage("Actor/Actriz y Datos Personales se han actualizado correctamente");
        } else {
            Utilities::setErrorMessage("Actor/Actriz actualizado, pero los datos personales no se han actualizado correctamente.");
        }
    }

    private function hasValidActorIdType($id): bool
    {
        if (ActorValidation::isInvalidIdDataType($id)) {
            error_log("Validation exception: ID del actor/actriz inválido. Debe contener solo números y ser mayor a cero - [{$id}]");
            Utilities::setErrorMessage("Actor/Actriz [{$id}] inválido");
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
            Utilities::setErrorMessage("Por favor verificar la información ingresada.");
        }

        return $inputInvalid;
    }
}

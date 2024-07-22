<?php
require_once '../../utils/SessionStart.php';
require_once('../../models/Director.php');
require_once('../../utils/Constants.php');
require_once '../../utils/Utilities.php';
require_once('validations/CommonValidation.php');
require_once('validations/DirectorValidation.php');
require_once('PersonController.php');

class DirectorController extends PersonController
{

    function showAllDirectors(): array
    {
        $model = new Director();
        $directorList = $model->getAll();
        $directorObjectArray = [];

        foreach ($directorList as $item) {
            $directorObject = $this->makeDirector($item);
            array_push($directorObjectArray, $directorObject);
        }
        return $directorObjectArray;
    }

    function showDirectorById($id): mixed
    {
        if (!$this->hasValidDirectorIdType($id)) {
            return false;
        }

        $model = new Director($id);
        $directorSaved = $model->getById();

        if (!$directorSaved) {
            $_SESSION['error_message'] = "Director [{$id}] no encontrado";
            error_log("Database exception: ID del director no encontrado en la base de datos - [{$id}]");
            return false;
        }

        $director = $this->makeDirector($directorSaved);

        return $director;
    }

    function createDirector(array $directorData, array $personData): bool
    {
        $directorSaved = false;

        if ($this->hasInvalidPersonInputFields($personData) || $this->hasInvalidDirectorInputFields($directorData, false)) {
            return $directorSaved;
        }

        $personSaved = $this->createPerson($personData);

        if ($personSaved) {

            $beginningCareer = $directorData['beginningCareer'];
            $activeYears = $directorData['activeYears'];
            $biography = $directorData['biography'];
            $awards = $directorData['awards'];
            $personId = $personSaved->getId();
    
            $model = new Director(null, $beginningCareer, $activeYears, $biography, $awards, $personId);
    
            $directorSaved = $model->save();
    
            if ($directorSaved) {
    
                $_SESSION['success_message'] = 'Director se ha registrado correctamente.';
            }else{

                $this->deletePerson($personId);

                $_SESSION['error_message'] = 'La información del Director no se ha registrado correctamente.';
                error_log("Database exception: Falló al guardar el director - Inicio de Carrera [{$beginningCareer}] Años activo [{$activeYears}] ID Persona [{$personId}]");
            }
        }
        return $directorSaved;
    }

    function editDirector($directorId, array $directorData, array $personData): mixed
    {
        $directorEdited = false;

        $personId = $personData['id'];

        if (!$this->hasValidDirectorIdType($directorId) || !$this->hasValidPersonIdType($personId) || $this->hasInvalidDirectorInputFields($directorData, true) || $this->hasInvalidPersonInputFields($personData)) {
            return $directorEdited;
        }

        $beginningCareer = $directorData['beginningCareer'];
        $activeYears = $directorData['activeYears'];
        $biography = $directorData['biography'];
        $awards = $directorData['awards'];

        $model = new Director($directorId, $beginningCareer, $activeYears, $biography, $awards, $personId);

        $directorEdited = $model->update();

        if ($directorEdited) {

            $personEdited = $this->editPerson($personId, $personData);

            $this->handlePersonEditedMessage($personEdited);
            
        }else{
            $_SESSION['error_message'] = "Director no se ha actualizado correctamente.";
            error_log("Database exception: Fallo al editar el director - Id: [{$directorId}] Inicio de Carrera [{$beginningCareer}] Años activo [{$activeYears}] ID Persona [{$personId}]");
        }

        return $directorEdited;
    }

    function deleteDirector($id): bool
    {
        $directorDeleted = false;

        if (!$this->hasValidDirectorIdType($id)) {
            return $directorDeleted;
        }

        $model = new Director($id);

        $directorDeleted = $model->getById();

        if (!$directorDeleted) {
            $_SESSION['warning_message'] = "Director [{$id}] no registrado";
            error_log("Database exception: ID del director no encontrado en la base de datos - [{$id}]");
            return $directorDeleted;
        }

        $directorDeleted = $this->deletePerson($directorDeleted->getPersonId());

        if ($directorDeleted) {
            $_SESSION['success_message'] = "La información del Director [{$id}] se ha eliminado correctamente.";
        }else {
            $_SESSION['error_message'] = "La información del Director [{$id}] no se ha eliminado correctamente.";
            error_log("Database exception: Falló al eliminar el director - ID Director [{$id}]");
        }

        return $directorDeleted;
    }

    private function makeDirector(Director $source): Director
    {
        return new Director(
            $source->getId(),
            $source->getBeginningCareer(),
            $source->getActiveYears(),
            Utilities::convertCharacters(0, $source->getBiography()),
            Utilities::convertCharacters(0, $source->getAwards()),
            $source->getPersonId()
        );
    }

    private function handlePersonEditedMessage(bool $personEdited){
        if ($personEdited) {
            $_SESSION['success_message'] = 'Director y Datos Personales se han actualizado correctamente';
        } else {
            $_SESSION['error_message'] = 'Director actualizado, pero los datos personales no se han actualizado correctamente.';
        }
    }

    private function hasValidDirectorIdType($id): bool
    {
        if (DirectorValidation::isInvalidIdDataType($id)) {
            $_SESSION['error_message'] = "Director [{$id}] inválido";
            error_log("Validation exception: ID del director inválido. Debe contener solo números y ser mayor a cero - [{$id}]");
            return false;
        }
        return true;
    }

    private function hasInvalidDirectorInputFields($directorData, $edit): bool
    {
        $inputInvalid = false;

        if (empty($directorData['beginningCareer']) || CommonValidation::isInvalidDate($directorData['beginningCareer'])) {
            error_log("Validation exception: Fecha de inicio de carrera vacia o no cumple con un formato de fecha aceptado  - [{$directorData['beginningCareer']}]");
            $inputInvalid = true;
        }

        if (CommonValidation::isInvalidInteger($directorData['activeYears'])) {
            error_log("Validation exception: Años activo inválido. Debe contener solo números y ser mayor a cero  - [{$directorData['activeYears']}]");
            $inputInvalid = true;
        }

        if (CommonValidation::hasInvalidLength($directorData['biography'], Constants::BIOGRAPHY_LENGTH)) {
            error_log("Validation exception: Biografía vacia o supera la cantidad maxima de caracteres de " . Constants::BIOGRAPHY_LENGTH . " Longitud - [{" . strlen($directorData['biography']) . "}]");
            $inputInvalid = true;
        }

        if ($edit && PersonValidation::isInvalidIdDataType($directorData['personId'])) {
            error_log("Validation exception: ID de la persona inválido. Debe contener solo números y ser mayor a cero  - [{$directorData['personId']}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            $_SESSION['error_message'] = "Por favor verificar la información ingresada.";
        }

        return $inputInvalid;
    }
}

<?php
require_once '../../utils/SessionStart.php';
require_once('../../utils/Constants.php');
require_once('../../utils/Utilities.php');
require_once('../../models/Person.php');
require_once('validations/PersonValidation.php');
class PersonController
{
    function showPersonById($id): mixed
    {
        if (!$this->hasValidPersonIdType($id)) {
            return false;
        }

        $model = new Person($id);
        $personSaved = $model->getById();

        if (!$personSaved) {
            error_log("Database exception: ID de la persona no encontrado en la base de datos - [{$id}]");
            return false;
        }

        $person = new Person(
            $personSaved->getId(),
            ucfirst(strtolower($personSaved->getFirstName())),
            ucfirst(strtolower($personSaved->getLastName())),
            $personSaved->getBirthdate(),
            $personSaved->getCountryId()
        );

        return $person;
    }

    function createPerson($personData): mixed
    {
        $personSaved = false;
        
        $firstName = $personData['firstName'];
        $lastName = $personData['lastName'];
        $birthdate = $personData['birthdate'];
        $countryId = $personData['countryId'];

        $firstName = strtoupper($firstName);
        $lastName = strtoupper($lastName);
        $birthdate = Utilities::changeDateFormat($birthdate, Constants::DATE_INPUT_FORMAT);

        $model = new Person(null, $firstName, $lastName, $birthdate, $countryId);
        $personSaved = $model->save();

        if (!$personSaved) {
            error_log("Database exception: Fallo al guardar la persona - Primer Nombre [{$firstName}] Primer Apellido [{$lastName}] Fecha de Nacimiento [{$birthdate}] Nacionalidad [{$countryId}]");
        }

        return $personSaved;
    }

    function editPerson($id, $personData): bool
    {
        $personEdited = false;

        $firstName = $personData['firstName'];
        $lastName = $personData['lastName'];
        $birthdate = $personData['birthdate'];
        $countryId = $personData['countryId'];

        $model = new Person(
            $id,
            strtoupper($firstName),
            strtoupper($lastName),
            Utilities::changeDateFormat($birthdate, Constants::DATE_INPUT_FORMAT),
            $countryId
        );

        $personEdited = $model->update();

        if (!$personEdited) {
            error_log("Database exception: Fallo al editar la persona - Id: [{$id}] Primer Nombre: [{$firstName}] Primer Apellido: [{$lastName}] Fecha de Nacimiento: [{$birthdate}] Id Pais: [{$countryId}]");
        }

        return $personEdited;
    }

    function deletePerson($id): bool
    {
        $personDeleted = false;

        if (!$this->hasValidPersonIdType($id)) {
            return $personDeleted;
        }

        $model = new Person($id);

        $personDeleted = $model->getById();

        if (!$personDeleted) {
            error_log("Database exception: ID de la persona no encontrada en la base de datos - [{$id}]");
            return $personDeleted;
        }

        $personDeleted = $model->delete();

        if (!$personDeleted) {
            error_log("Database exception: Fallo al eliminar la persona - ID [{$id}]");
        }

        return $personDeleted;
    }

    protected function hasValidPersonIdType($id): bool
    {
        if (PersonValidation::validateIdDataType($id)) {
            error_log("Validation exception: ID de la persona inválido. Debe contener solo números y ser mayor a cero - [{$id}]");
            return false;
        }
        return true;
    }

    protected function hasInvalidPersonInputFields($personData): bool
    {
        $inputInvalid = false;

        if (PersonValidation::validateNamesLastNameType($personData['firstName'])) {
            error_log("Validation exception: Primer Nombre vacio o no es una cadena válida de carácteres alfabéticos - [{$personData['firstName']}]");
            $inputInvalid = true;
        }

        if (PersonValidation::validateNamesLastNameType($personData['lastName'])) {
            error_log("Validation exception: Primer Apellido vacio o no es una cadena válida de carácteres alfabéticos - [{$personData['lastName']}]");
            $inputInvalid = true;
        }

        if (PersonValidation::validateIdDataType($personData['countryId'])) {
            error_log("Validation exception: ID del país inválido. Debe contener solo números y ser mayor a cero  - [{$personData['countryId']}]");
            $inputInvalid = true;
        }

        if (empty($personData['birthdate'])) {
            error_log("Validation exception: Fecha de nacimiento inválida. No debe ser nula  - [{$personData['birthdate']}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            $_SESSION['error_message'] = "Por favor verificar la información ingresada.";
        }

        return $inputInvalid;
    }
}

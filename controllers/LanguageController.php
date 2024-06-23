<?php
require_once '../../utils/session_start.php';
require_once('validations/LanguageValidation.php');
require_once('../../models/Language.php');
class LanguageController
{

    function showAll(): array
    {
        $model = new Language();
        $languageList = $model->getAll();
        $languageObjectArray = [];
        foreach ($languageList as $item) {
            $languageObject = new Language($item->getId(), $item->getName(), $item->getIsoCode());
            array_push($languageObjectArray, $languageObject);
        }
        return $languageObjectArray;
    }

    function create($name, $isoCode): bool
    {
        $languageSaved = false;
        $isoCode = strtoupper($isoCode);
        $inputInvalid = $this->validInputFields($name, $isoCode);

        if (!$inputInvalid) {

            $model = new Language(null, $name, $isoCode);
            $languageSaved = $model->save();

            if ($languageSaved) {
                $_SESSION['success_message'] = 'Idioma creado correctamente.';
            } else {
                $_SESSION['error_message'] = 'El Idioma no se ha creado correctamente.';
                error_log("Database exception: Fallo al guardar el idioma - Nombre [{$name}] ISO Code [{$isoCode}]");
            }
        }

        return $languageSaved;
    }

    function edit($id, $name, $isoCode): bool
    {
        $languageSaved = false;
        $isoCode = strtoupper($isoCode);
        $inputInvalid = $this->validInputFields($name, $isoCode);

        if (!$inputInvalid) {

            $model = new Language($id, $name, $isoCode);
            $languageSaved = $model->update();

            if ($languageSaved) {
                $_SESSION['success_message'] = 'Idioma editado correctamente.';
            } else {
                $_SESSION['error_message'] = 'El Idioma no se ha editado correctamente.';
                error_log("Database exception: Fallo al actualizar el idioma - ID [{$id}] Nombre [{$name}] ISO Code [{$isoCode}]");
            }
        }

        return $languageSaved;
    }

    function delete($id): bool
    {
        $languageDeleted = false;

        $this->validIdType($id);

        $model = new Language($id, null, null);

        $languageSaved = $model->getById();

        if (!$languageSaved) {
            $_SESSION['warning_message'] = "Idioma no registrado";
            error_log("Database exception: ID de idioma no encontrado en la base de datos - [{$id}]");
            return $languageDeleted;
        }

        $languageDeleted = $model->delete();

        if ($languageDeleted) {
            $_SESSION['success_message'] = 'Idioma eliminado correctamente.';
        } else {
            $_SESSION['error_message'] = 'El Idioma no se ha eliminado correctamente.';
            error_log("Database exception: Fallo al eliminar el idioma - ID [{$id}]");
        }

        return $languageDeleted;
    }

    function showById($id): mixed
    {
        $this->validIdType($id);

        $model = new Language($id, null, null);
        $languageSaved = $model->getById();

        if (!$languageSaved) {
            $_SESSION['error_message'] = "Idioma inválido";
            error_log("Database exception: ID de idioma no encontrado en la base de datos - [{$id}]");
            return false;
        }

        return $languageSaved;
    }

    function checkByIsoCode($isoCode): bool
    {
        $languageExist = false;
        $model = new Language(null, null, $isoCode);
        $languageExist = $model->getByIsoCode();
        return $languageExist;
    }

    function validInputFields($name, $isoCode): bool
    {
        $inputInvalid = false;
        $inputAlreadyRegister = false;

        if (LanguageValidation::validateName($name)) {
            error_log("Validation exception: Nombre vacio o no es una cadena válida de carácteres alfabéticos - [{$name}]");
            $inputInvalid = true;
        }

        if (LanguageValidation::validateIsoCode($isoCode)) {
            error_log("Validation exception: Código ISO vacio o no es una cadena válida de 3 carácteres alfabéticos o númericos  - [{$isoCode}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            $_SESSION['error_message'] = "Por favor verificar las entradas, no son válidas.";
            return $inputInvalid;
        }

        $inputAlreadyRegister = $this->checkByIsoCode($isoCode);

        if ($inputAlreadyRegister) {
            $_SESSION['warning_message'] = "Ya existe un idioma registrado para el ISO Code [{$isoCode}]";
            error_log("Database exception: El idioma ya se encuentran registrado - Nombre [{$name}] ISO Code [{$isoCode}]");
            $inputInvalid = true;
        }

        return $inputInvalid;
    }

    function validIdType($id): bool
    {
        if (!ctype_digit($id)) {
            $_SESSION['error_message'] = "Idioma inválido";
            error_log("Database exception: ID de idioma inválido. Debe contener solo números - [{$id}]");
            return false;
        }
        return true;
    }
}

<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/Language.php');
require_once('validations/LanguageValidation.php');
class LanguageController
{

    function showAll(): array
    {
        $model = new Language();
        $languageList = $model->getAll();
        $languageObjectArray = [];
        foreach ($languageList as $item) {
            $languageObject = new Language($item->getId(), ucfirst(Utilities::convertCharacters(0, $item->getName())), $item->getIsoCode());
            array_push($languageObjectArray, $languageObject);
        }
        return $languageObjectArray;
    }

    function showById($id): mixed
    {
        if (!$this->checkValidIdDataType($id)) {
            return false;
        }

        $model = new Language($id);
        $languageSaved = $model->getById();

        if (!$languageSaved) {
            error_log("Database exception: ID de idioma no encontrado en la base de datos - [{$id}]");
            Utilities::setWarningMessage("Idioma [{$id}] no registrado");
            return false;
        }

        $language = new Language(
            $languageSaved->getId(),
            ucfirst(Utilities::convertCharacters(0, $languageSaved->getName())),
            $languageSaved->getIsoCode()
        );

        return $language;
    }
    
    function create($name, $isoCode): bool
    {
        $languageSaved = false;

        $name = trim($name);
        $isoCode = trim($isoCode);

        $name = strtoupper($name);
        $isoCode = strtoupper($isoCode);

        if ($this->checkValidInputFields($name, $isoCode, true)) {
            return $languageSaved;
        }

        $model = new Language(null, $name, $isoCode);
        $languageSaved = $model->save();

        if ($languageSaved) {
            Utilities::setSuccessMessage("Idioma creado correctamente.");
        } else {
            error_log("Database exception: Fallo al guardar el idioma - Nombre [{$name}] ISO Code [{$isoCode}]");
            Utilities::setErrorMessage("El Idioma no se ha creado correctamente.");
        }

        return $languageSaved;
    }

    function edit($id, $name, $isoCode): bool
    {
        $languageEdited = false;

        $name = trim($name);
        $isoCode = trim($isoCode);
        
        $name = strtoupper($name);
        $isoCode = strtoupper($isoCode);

        if (!$this->checkValidIdDataType($id) || $this->checkValidInputFields($name, $isoCode, false)) {
            return $languageEdited;
        }

        $model = new Language($id, $name, $isoCode);
        $languageEdited = $model->update();

        if ($languageEdited) {
            Utilities::setSuccessMessage("Idioma editado correctamente.");
        } else {
            error_log("Database exception: Fallo al actualizar el idioma - ID [{$id}] Nombre [{$name}] ISO Code [{$isoCode}]");
            Utilities::setErrorMessage("El Idioma no se ha editado correctamente.");
        }

        return $languageEdited;
    }

    function delete($id): bool
    {
        $languageDeleted = false;

        if (!$this->checkValidIdDataType($id)) {
            return $languageDeleted;
        }

        $model = new Language($id);

        $languageSaved = $model->getById();

        if (!$languageSaved) {
            error_log("Database exception: ID de idioma no encontrado en la base de datos - [{$id}]");
            Utilities::setWarningMessage("Idioma [{$id}] no registrado");
            return $languageDeleted;
        }

        $languageDeleted = $model->delete();

        if ($languageDeleted) {
            Utilities::setSuccessMessage("Idioma eliminado correctamente.");
        } else {
            error_log("Database exception: Fallo al eliminar el idioma - ID [{$id}]");
            Utilities::setErrorMessage("El Idioma no se ha eliminado correctamente.");
        }

        return $languageDeleted;
    }

    function checkByIsoCode($isoCode): bool
    {
        $languageExist = false;
        $model = new Language(null, null, $isoCode);
        $languageExist = $model->getByIsoCode();
        return is_bool($languageExist) ? false : true;
    }

    function checkValidInputFields($name, $isoCode, $create): bool
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
            Utilities::setErrorMessage("Por favor verificar las entradas, no son válidas.");
            return $inputInvalid;
        }

        if($create){
            $inputAlreadyRegister = $this->checkByIsoCode($isoCode);

            if ($inputAlreadyRegister) {
                error_log("Database exception: El idioma ya se encuentran registrado - Nombre [{$name}] ISO Code [{$isoCode}]");
                Utilities::setWarningMessage("Ya existe un idioma registrado para el ISO Code [{$isoCode}]");
                $inputInvalid = true;
            }
        }
    
        return $inputInvalid;
    }

    function checkValidIdDataType($id): bool
    {
        if (LanguageValidation::isInvalidIdDataType($id)) {
            error_log("Validation exception: ID de idioma inválido. Debe contener solo números - [{$id}]");
            Utilities::setErrorMessage("Idioma [{$id}] inválido");
            return false;
        }
        return true;
    }
}

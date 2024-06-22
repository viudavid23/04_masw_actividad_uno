<?php
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
        session_start();

        $languageSaved = false;
        $isoCode = strtoupper($isoCode);
        $inputInvalid = $this->validInputFields($name, $isoCode);

        if(!$inputInvalid) {

            $model = new Language(null, $name, $isoCode);
            $languageSaved = $model->save();
            
            if($languageSaved) {
                $_SESSION['success_message'] = 'Idioma creado correctamente.';
            }else{
                $_SESSION['error_message'] = 'El Idioma no se ha creado correctamente.';
                error_log("Database error: Fallo al guardar el idioma - {$name}, {$isoCode}");
            }
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
            error_log("Validation error: Nombre vacio o no es una cadena válida de carácteres alfabéticos - {$name}");
            $inputInvalid = true;
        }

        if (LanguageValidation::validateIsoCode($isoCode)) {
            error_log("Validation error: Código ISO vacio o no es una cadena válida de 3 carácteres alfabéticos o númericos  - {$isoCode}");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            $_SESSION['error_message'] = "Por favor verificar que las entradas sean válidas.";
            return $inputInvalid;
        }

        $inputAlreadyRegister = $this->checkByIsoCode($isoCode);

        if($inputAlreadyRegister) {
            error_log("Database error: El idioma ya se encuentran registrado - {$name}, {$isoCode}");
            $_SESSION['warning_message'] = "Ya existe un idioma registrado para el ISO Code {$isoCode}";
            $inputInvalid = true;
        }

        return $inputInvalid;
    }
}

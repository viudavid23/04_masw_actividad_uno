<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/Platform.php');
require_once('validations/CommonValidation.php');
require_once('validations/PlatformValidation.php');
class PlatformController
{

    function showAll(): array
    {
        $model = new Platform();
        $platformList = $model->getAll();
        $platformObjectArray = [];
        foreach ($platformList as $item) {
            $platformObject = $this->makePlatform($item);
            array_push($platformObjectArray, $platformObject);
        }
        return $platformObjectArray;
    }

    function showById($id): mixed
    {
        if (!$this->validateIdType($id)) {
            return false;
        }

        $model = new Platform($id);
        $platformSaved = $model->getById();

        if (!$platformSaved) {
            $_SESSION['error_message'] = "Plataforma [{$id}] inválida";
            error_log("Database exception: ID de la plataforma no encontrado en la base de datos - [{$id}]");
            return false;
        }

        $platform = $this->makePlatform($platformSaved);

        return $platform;
    }

    function create($platformData): bool
    {
        $platformSaved = false;

        if ($this->validateInvalidInputFields($platformData, true)) {
            return $platformSaved;
        }

        $name = strtoupper($platformData['name']);
        $description = strtoupper($platformData['description']);
        $releaseDate = $platformData['releaseDate'];
        $logo = $platformData['logo'];

        $model = new Platform(null, $name, $description, $releaseDate, $logo);
        $platformSaved = $model->save();

        if ($platformSaved) {
            $_SESSION['success_message'] = 'Plataforma creada correctamente.';
        } else {
            $_SESSION['error_message'] = 'La Plataforma no se ha creado correctamente.';
            error_log("Database exception: Falló al guardar la plataforma - Nombre [{$name}] Descripción [{$description}] Fecha Lanzamiento [{$releaseDate}] Logo [{$logo}]");
        }

        return $platformSaved;
    }

    function edit($id, $platformData): bool
    {
        $platformEdited = false;

        if (!$this->validateIdType($id) || $this->validateInvalidInputFields($platformData, false)) {
            return $platformEdited;
        }

        $name = strtoupper($platformData['name']);
        $description = strtoupper($platformData['description']);
        $releaseDate = $platformData['releaseDate'];
        $logo = $platformData['logo'];

        $model = new Platform($id, $name, $description, $releaseDate, $logo);
        $platformEdited = $model->update();

        if ($platformEdited) {
            $_SESSION['success_message'] = 'Plataforma editada correctamente.';
        } else {
            $_SESSION['error_message'] = 'La Plataforma no se ha editado correctamente.';
            error_log("Database exception: Falló al actualizar la plataforma - ID [{$id}] Nombre [{$name}] Descripción [{$platformData['description']}] Fecha Lanzamiento [{$platformData['releaseDate']}] Logo [{$platformData['logo']}] ");
        }

        return $platformEdited;
    }

    function delete($id): bool
    {
        $platformDeleted = false;

        if (!$this->validateIdType($id)) {
            return $platformDeleted;
        }

        $model = new Platform($id);

        $platformSaved = $model->getById();

        if (!$platformSaved) {
            $_SESSION['warning_message'] = "Plataforma [{$id}] no registrada";
            return $platformDeleted;
        }

        $platformDeleted = $model->delete();

        if ($platformDeleted) {
            $_SESSION['success_message'] = 'Plataforma eliminada correctamente.';
        } else {
            $_SESSION['error_message'] = 'La Plataforma no se ha eliminado correctamente.';
            error_log("Database exception: Falló al eliminar la plataforma - ID [{$id}]");
        }

        return $platformDeleted;
    }

    function makePlatform(Platform $source): Platform
    {
        return new Platform(
            $source->getId(),
            ucfirst(Utilities::convertCharacters(0, $source->getName())),
            ucfirst(Utilities::convertCharacters(0, $source->getDescription())),
            $source->getReleaseDate(),
            $source->getLogo()
        );
    }

    function checkByName($name): bool
    {
        $model = new Platform(null, $name);
        $plarformExist = $model->checkByName();
        return $plarformExist;
    }

    function validateInvalidInputFields($platformData, $create): bool
    {
        $inputInvalid = false;
        $inputAlreadyRegister = false;

        if (!empty($platformData['description']) && PlatformValidation::validateString($platformData['description'])) {
            error_log("Validation exception: Descripción no es una cadena válida de carácteres alfabéticos - [{$platformData['description']}]");
            $inputInvalid = true;
        }

        if (!empty($platformData['releaseDate']) && CommonValidation::isInvalidDate($platformData['releaseDate'])) {
            error_log("Validation exception: Fecha de lanzamiento no cumple con un formato de fecha aceptado  - [{$platformData['releaseDate']}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            $_SESSION['error_message'] = "Por favor verificar la información ingresada.";
            return $inputInvalid;
        }

        if ($create) {
            $inputAlreadyRegister = $this->checkByName($platformData['name']);

            if ($inputAlreadyRegister) {
                $_SESSION['warning_message'] = "El Nombre de la plataforma ya se encuentra en uso [{$platformData['name']}]";
                error_log("Database exception: La plataforma ya se encuentra en uso - Nombre [{$platformData['name']}] Descripción [{$platformData['description']}] Fecha de Lanzamiento [{$platformData['releaseDate']}] ");
                $inputInvalid = true;
            }
        }

        return $inputInvalid;
    }

    function validateIdType($id): bool
    {
        if (PlatformValidation::validateIdDataType($id)) {
            $_SESSION['error_message'] = "Plataforma [{$id}] inválida";
            error_log("Validation exception: ID de la plataforma inválido. Debe contener solo números - [{$id}]");
            return false;
        }
        return true;
    }
}

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

    function showById($platformId): mixed
    {
        if ($this->checkInvalidIdDataType($platformId)) {
            return false;
        }

        $model = new Platform($platformId);
        $platformSaved = $model->getById();

        if (!$platformSaved) {
            error_log("[PlatformController] [Database exception] ID no encontrado en la tabla platform de la base de datos - [{$platformId}]");
            Utilities::setWarningMessage("Plataforma [{$platformId}] no registrada");
            return false;
        }

        return $this->makePlatform($platformSaved);
    }

    function create($platformData): bool
    {

        if ($this->checkInvalidInputFields($platformData) || $this->checkAlreadyRegisterInputPlatformName($platformData['name'])) {
            return false;
        }

        $name = strtoupper($platformData['name']);
        $description = strtoupper($platformData['description']);
        $releaseDate = $platformData['releaseDate'];
        $logo = $platformData['logo'];

        $model = new Platform(null, $name, $description, $releaseDate, $logo);
        $platformSaved = $model->save();

        if (!$platformSaved) {
            error_log("[PlatformController] [Database exception] Falló al registrar en la tabla platform de la base de datos - name: [{$name}] description: [{$description}] release_date: [{$releaseDate}] Logo [{$logo}]");
            Utilities::setErrorMessage("La Plataforma [{$name}] no se ha creado correctamente.");
            return false;
        } else {
            Utilities::setSuccessMessage("Plataforma [{$name}] creada correctamente.");
            return true;
        }
    }

    function edit($platformId, $platformData): bool
    {

        if ($this->checkInvalidIdDataType($platformId) || $this->checkInvalidInputFields($platformData)) {
            return false;
        }

        $name = strtoupper($platformData['name']);
        $description = strtoupper($platformData['description']);
        $releaseDate = $platformData['releaseDate'];
        $logo = $platformData['logo'];

        $model = new Platform($platformId, $name, $description, $releaseDate, $logo);
        $platformEdited = $model->update();

        if (!$platformEdited) {
            error_log("[PlatformController] [Database exception] Falló al actualizar la tabla platform de la base de datos - id: [{$platformId}] name: [{$name}] description: [{$platformData['description']}] release_date: [{$platformData['releaseDate']}] logo: [{$platformData['logo']}] ");
            Utilities::setErrorMessage("La Plataforma [{$name}] no se ha editado correctamente.");
            return false;
        } else {
            Utilities::setSuccessMessage("Plataforma [{$name}] editada correctamente.");
            return true;
        }
    }

    function delete($platformId): bool
    {

        if ($this->checkInvalidIdDataType($platformId)) {
            return false;
        }

        $model = new Platform($platformId);

        $platformSaved = $model->getById();

        if (!$platformSaved) {
            Utilities::setWarningMessage("Plataforma [{$platformId}] no registrada");
            return false;
        }

        $platformDeleted = $model->delete();

        if (!$platformDeleted) {
            error_log("[PlatformController] [Database exception] Falló al eliminar ID de la tabla platform de la base de datos - ID [{$platformId}]");
            Utilities::setErrorMessage("La Plataforma [{$platformId}] no se ha eliminado correctamente.");
        } else {
            Utilities::setSuccessMessage("Plataforma [{$platformId}] eliminada correctamente.");
        }

        return $platformDeleted;
    }

    private function makePlatform(Platform $source): Platform
    {
        return new Platform(
            $source->getId(),
            ucfirst(Utilities::convertCharacters(0, $source->getName())),
            ucfirst(Utilities::convertCharacters(0, $source->getDescription())),
            $source->getReleaseDate(),
            $source->getLogo()
        );
    }

    private function findMatchesByName($name): bool
    {
        $model = new Platform(null, $name);
        $plarformExist = $model->findMatchesByName();
        return $plarformExist;
    }

    private function checkInvalidInputFields($platformData): bool
    {
        $inputInvalid = false;

        if (!empty($platformData['description']) && PlatformValidation::validateString($platformData['description'])) {
            error_log("[PlatformController] [Validation exception] Descripción no es una cadena válida de carácteres alfabéticos - [{$platformData['description']}]");
            $inputInvalid = true;
        }

        if (!empty($platformData['releaseDate']) && CommonValidation::isInvalidDate($platformData['releaseDate'])) {
            error_log("[PlatformController] [Validation exception] Fecha de lanzamiento no cumple con un formato de fecha aceptado  - [{$platformData['releaseDate']}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            Utilities::setErrorMessage("Por favor verificar la información ingresada.");
            return $inputInvalid;
        }

        return $inputInvalid;
    }

    private function checkInvalidIdDataType($id): bool
    {
        if (PlatformValidation::isInvalidIdDataType($id)) {
            error_log("[PlatformController] [Validation exception] ID de la plataforma inválido. Debe contener solo números - [{$id}]");
            Utilities::setErrorMessage("Plataforma [{$id}] inválida");
            return true;
        }
        return false;
    }

    private function checkAlreadyRegisterInputPlatformName($platformName): bool
    {
        $inputAlreadyRegister = $this->findMatchesByName($platformName);

        if ($inputAlreadyRegister) {
            error_log("[PlatformController] [Database exception] El campo name ya se encuentra registrado en la tabla platform de la base de datos - name [{$platformName}] ");
            Utilities::setWarningMessage("La plataforma [{$platformName}] ya se encuentra en uso");
            return true;
        }

        return false;
    }
}

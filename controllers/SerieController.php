<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/Serie.php');
require_once('validations/CommonValidation.php');
require_once('validations/SerieValidation.php');
class SerieController
{

    function showAll(): array
    {
        $model = new Serie();
        $serieList = $model->getAll();
        $serieObjectArray = [];
        foreach ($serieList as $item) {
            $serieObject = $this->makeSerie($item);
            array_push($serieObjectArray, $serieObject);
        }

        return $serieList;
    }

    function showById($id): mixed
    {
        if (!$this->validateIdType($id)) {
            return false;
        }

        $model = new Serie($id);
        $serieSaved = $model->getById();

        if (!$serieSaved) {
            $_SESSION['error_message'] = "Serie [{$id}] inválida";
            error_log("Database exception: ID de la serie no encontrado en la base de datos - [{$id}]");
            return false;
        }

        $serie = $this->makeSerie($serieSaved);

        return $serie;
    }

    function create($serieData): bool
    {
        $serieSaved = false;

        if ($this->validateInvalidInputFields($serieData)) {
            return $serieSaved;
        }

        $title = strtoupper($serieData['title']);
        $synopsis = strtoupper($serieData['synopsis']);
        $releaseDate = $serieData['releaseDate'];

        $model = new Serie(null, $title, $synopsis, $releaseDate);
        $serieSaved = $model->save();

        if ($serieSaved) {
            $_SESSION['success_message'] = 'Serie creada correctamente.';
        } else {
            $_SESSION['error_message'] = 'La Serie no se ha creado correctamente.';
            error_log("Database exception: Falló al guardar la serie - Titulo [{$title}] Sinopsis [{$synopsis}] Fecha Lanzamiento [{$releaseDate}]");
        }

        return $serieSaved;
    }

    function edit($id, $serieData): bool
    {
        $serieEdited = false;

        if (!$this->validateIdType($id) || $this->validateInvalidInputFields($serieData)) {
            return $serieEdited;
        }

        $title = strtoupper($serieData['title']);
        $synopsis = strtoupper($serieData['synopsis']);
        $releaseDate = $serieData['releaseDate'];

        $model = new Serie($id, $title, $synopsis, $releaseDate);
        $serieEdited = $model->update();

        if ($serieEdited) {
            $_SESSION['success_message'] = 'Serie editada correctamente.';
        } else {
            $_SESSION['error_message'] = 'La Serie no se ha editado correctamente.';
            error_log("Database exception: Falló al actualizar la serie - ID [{$id}] Titulo [{$title}] Sinopsis [{$synopsis}] Fecha Lanzamiento [{$releaseDate}]");
        }

        return $serieEdited;
    }

    function delete($id): bool
    {
        $serieDeleted = false;

        if (!$this->validateIdType($id)) {
            return $serieDeleted;
        }

        $model = new Serie($id);

        $serieSaved = $model->getById();

        if (!$serieSaved) {
            $_SESSION['warning_message'] = "Serie [{$id}] no registrada";
            return $serieDeleted;
        }

        $serieDeleted = $model->delete();

        if ($serieDeleted) {
            $_SESSION['success_message'] = 'Serie eliminada correctamente.';
        } else {
            $_SESSION['error_message'] = 'La Serie no se ha eliminado correctamente.';
            error_log("Database exception: Falló al eliminar la Serie - ID [{$id}]");
        }

        return $serieDeleted;
    }

    function makeSerie(Serie $source): Serie
    {
        return new Serie(
            $source->getId(),
            ucfirst(Utilities::convertCharacters(0, $source->getTitle())),
            $source->getSynopsis(),
            $source->getReleaseDate()
        );
    }

    function validateInvalidInputFields($serieData): bool
    {
        $inputInvalid = false;

        if (empty($serieData['title']) || CommonValidation::validateLength($serieData['title'], 50)) {
            error_log("Validation exception: Titulo no es una cadena válida o excede el límite de 50 carácteres alfabéticos- [{$serieData['title']}]");
            $inputInvalid = true;
        }

        if (empty($serieData['synopsis']) || CommonValidation::validateLength($serieData['synopsis'], Constants::SYNOPSIS_LENGTH)) {
            error_log("Validation exception: Sinopsis no es una cadena válida o excede el límite de " . Constants::SYNOPSIS_LENGTH . " carácteres alfabéticos- [{$serieData['synopsis']}]");
            $inputInvalid = true;
        }

        if (empty($serieData['releaseDate']) || CommonValidation::isInvalidDate($serieData['releaseDate'])) {
            error_log("Validation exception: Fecha de lanzamiento no cumple con un formato de fecha aceptado  - [{$serieData['releaseDate']}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            $_SESSION['error_message'] = "Por favor verificar la información ingresada.";
            return $inputInvalid;
        }

        return $inputInvalid;
    }

    function validateIdType($id): bool
    {
        if (SerieValidation::validateIdDataType($id)) {
            $_SESSION['error_message'] = "Serie [{$id}] inválida";
            error_log("Validation exception: ID de la serie inválido. Debe contener solo números - [{$id}]");
            return false;
        }
        return true;
    }
}

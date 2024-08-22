<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('exceptions/RecordNotFoundException.php');
require_once('../../models/PlatformSerie.php');
require_once('PlatformController.php');
class PlatformSerieController
{

    function showBySerieId($serieId): mixed
    {

        $platformSerieObject = [];

        try {
            $this->checkValidSerieIdDataType($serieId);

            $model = new PlatformSerie(null, $serieId);

            $platformSerieSaved = $model->getBySerieId();

            if (count($platformSerieSaved) == 0) {
                error_log("[PlatformSerieController] [Data Error] ID de la serie no encontrado en la tabla platform_serie de la base de datos - [{$serieId}]");
                return false;
            }

            foreach($platformSerieSaved as $platformSerie) {
                $platformSerieObject[] =  new PlatformSerie($platformSerie->getPlatformId(), $platformSerie->getSerieId(), $platformSerie->getStatus());
            }

            return $platformSerieObject;
        } catch (InvalidArgumentException $e) {
            error_log("[PlatformSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function showByPlatformId($platformId): mixed
    {
        $platformSerieObject = [];

        try {
            $this->checkValidSerieIdDataType($platformId);

            $model = new PlatformSerie($platformId, null);

            $platformSerieSaved = $model->getByPlatformId();

            if (count($platformSerieSaved) == 0) {
                error_log("[PlatformSerieController] [Data Error] ID de la plataforma no encontrado en la tabla platform_serie de la base de datos - [{$platformId}]");
                return false;
            }

            foreach($platformSerieSaved as $platformSerie) {
                $platformSerieObject[] =  new PlatformSerie($platformSerie->getPlatformId(), $platformSerie->getSerieId(), $platformSerie->getStatus());
            }

            return $platformSerieObject;
        } catch (InvalidArgumentException $e) {
            error_log("[PlatformSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function create($serieId, $platformIdsData): bool
    {
        try {
            $this->checkValidSerieIdDataType($serieId);
            $this->checkValidPlatformSerieInputFields($platformIdsData);
            $this->checkValidPlatform($platformIdsData);

            $model = new PlatformSerie(null, $serieId);
            $platformSerieSaved = $model->save($platformIdsData);

            if ($platformSerieSaved) {
                return true;
            }

            $platformIdsDecode = json_encode($platformIdsData);
            error_log("[PlatformSerieController] [Data Error] Falló al guardar las plataformas [{$platformIdsDecode}] de la serie [{$serieId}] en la tabla platform_serie");
            throw new RuntimeException("Las plataformas [{$platformIdsDecode}] de la serie [{$serieId}] no se han creado correctamente.", Constants::INTERNAL_SERVER_ERROR_CODE);
        } catch (RecordNotFoundException $e) {
            error_log("[ActorSerieController] [Record Not Found Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (InvalidArgumentException $e) {
            error_log("[PlatformSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[PlatformSerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function edit($serieId, $newPlatformIds): bool
    {
        try {

            $this->checkValidSerieIdDataType($serieId);
            $this->checkValidPlatformSerieInputFields($newPlatformIds);
            $this->checkValidPlatform($newPlatformIds);

            $platformSerieSaved = $this->showBySerieId($serieId);
            $platformSerieIds = [];

            foreach ($platformSerieSaved as $item) {
                $platformSerieIds[] = $item->getPlatformId();
            }

            $this->saveNewSeriePlatforms($serieId, $newPlatformIds, $platformSerieIds);

            $platformSerieToUpdate = $this->setSeriePlatformToUpdate($newPlatformIds, $platformSerieIds);

            $model = new PlatformSerie(null, $serieId);

            $platformSerieEdited = $model->update($platformSerieToUpdate);

            if ($platformSerieEdited) {
                return true;
            }

            $platformIdsDecode = json_encode($newPlatformIds);
            error_log("[PlatformSerieController] [Data Error] Falló al actualizar las plataformas [{$platformIdsDecode}] de la serie [{$serieId}] en la tabla platform_serie");
            throw new RuntimeException("Las plataformas [{$platformIdsDecode}] de la serie [{$serieId}] no se han editado correctamente.", Constants::INTERNAL_SERVER_ERROR_CODE);
        } catch (RecordNotFoundException $e) {
            error_log("[PlatformSerieController] [Record Not Found Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (InvalidArgumentException $e) {
            error_log("[PlatformSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[PlatformSerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    private function setSeriePlatformToUpdate(array $newPlatformIds, array $platformSerieIdsSaved): array
    {
        $platformSerieToUpdate = [];
        foreach ($platformSerieIdsSaved as $currentPlatformSerieItem) {

            $platformSerieToUpdate[$currentPlatformSerieItem] = in_array($currentPlatformSerieItem, $newPlatformIds) ? 1 : 0;
        }

        return $platformSerieToUpdate;
    }

    private function saveNewSeriePlatforms(int $serieId, array $newPlatformIds, array $platformSerieIdsSaved): void
    {
        $newPlatformSerie = array_diff($newPlatformIds, $platformSerieIdsSaved);

        if (!empty($newPlatformSerie)) {
            $this->create($serieId, $newPlatformSerie);
        }
    }

    function delete($id): bool
    {
        $serieDeleted = false;

        if (!$this->checkValidSerieIdDataType($id)) {
            return $serieDeleted;
        }

        $model = new Serie($id);

        $serieSaved = $model->getById();

        if (!$serieSaved) {
            error_log("[PlatformSerieController] [Data Error] Serie no encontrada en la tabla platform_serie de la base de datos - SERIE_ID [{$id}]");
            return $serieDeleted;
        }

        $serieDeleted = $model->delete();

        if (!$serieDeleted) {
            error_log("[PlatformSerieController] [Data Error] Falló al eliminar de la base de datos la Serie en la tabla platform_serie - SERIE_ID [{$id}]");
        }

        return $serieDeleted;
    }


    private function checkValidPlatformSerieInputFields($platformIdsData): void
    {
        $inputInvalid = false;

        if (CommonValidation::isInvalidIntegerList($platformIdsData)) {
            $platformIdsEncode = json_encode($platformIdsData);
            error_log("[PlatformSerieController] [Validation Error] Listado de plataformas de la serie no enviado o no contiene solo números enteros positivos - [{$platformIdsEncode}]");
            $inputInvalid = true;
        }

        if ($inputInvalid) {
            throw new InvalidArgumentException("Por favor verificar la información ingresada.", Constants::BAD_REQUEST_CODE);
        }
    }

    private function checkValidSerieIdDataType($id): void
    {
        if (SerieValidation::isInvalidIdDataType($id)) {
            error_log("[PlatformSerieController] [Validation Error] ID de la serie inválido. Debe contener solo números - [{$id}]");
            throw new InvalidArgumentException("Id [{$id}] de la serie inválido", Constants::BAD_REQUEST_CODE);
        }
    }

    private function checkValidPlatform($platformIdsData)
    {
        $platformController = new PlatformController();
        foreach ($platformIdsData as $plaformItem) {
            $platformSaved = $platformController->showById($plaformItem);
            if (!$platformSaved) {
                error_log("[PlatformSerieController] [Data Error] ID de la plataforma no encontrado en la base de datos - [{$plaformItem}]");
                throw new RecordNotFoundException("Plataforma [{$plaformItem}] no registrada");
            }
        }
    }

    function getPlatformOptions(int $serieId): string
    {
        $options = '';

        $platformController = new PlatformController();

        $platformSerieList = $this->showBySerieId($serieId);

        $platformsList = $platformController->showAll();

        $selectedPlatformIds = [];
        if (isset($platformSerieList) && !empty($platformSerieList)) {

            foreach ($platformSerieList as $platformSerieItem) {
                if ($platformSerieItem->getStatus() == 1) {
                    $selectedPlatformIds[] = $platformSerieItem->getPlatformId();
                }
            }
        }

        foreach ($platformsList as $itemPlatform) {
            $platformId = $itemPlatform->getId();
            $platformOption = Utilities::concatStrings("[", $platformId, "]", " - ", $itemPlatform->getName());

            if (in_array($platformId, $selectedPlatformIds)) {
                $options .= '<option value="' . $platformId . '" selected=' . $platformId . '>' . $platformOption . '</option>';
            } else {
                $options .= '<option value="' . $platformId . '">' . $platformOption . '</option>';
            }
        }

        return $options;
    }

}

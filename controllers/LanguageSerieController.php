<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/LanguageSerie.php');
require_once('validations/SerieValidation.php');
require_once('validations/LanguageValidation.php');
require_once('LanguageController.php');
require_once('SerieController.php');
class LanguageSerieController
{

    function showBySerieId($serieId): mixed
    {
        try {
            $this->checkValidIdDataType($serieId);

            $languageSerieObjectListSaved = [];
            $model = new LanguageSerie($serieId);
            $languageSerieModelListSaved = $model->getBySerieId($serieId);

            if (!$languageSerieModelListSaved) {
                error_log("Database exception: ID de la serie no encontrado en la tabla language_serie de la base de datos - SERIE_ID [{$serieId}]");
                return false;
            }

            foreach ($languageSerieModelListSaved as $languageSerieModelItem) {
                $languageSerieObjectItem = new LanguageSerie($languageSerieModelItem->getSerieId(), $languageSerieModelItem->getLanguageId(), $languageSerieModelItem->getAudio(), $languageSerieModelItem->getSubtitle());
                array_push($languageSerieObjectListSaved, $languageSerieObjectItem);
            }

            return $languageSerieObjectListSaved;
        } catch (InvalidArgumentException $e) {
            error_log("[ActorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function showByLanguageId($languageId): mixed
    {

        try {
            $this->checkValidIdDataType($languageId);

            $languageSerieObjectListSaved = [];
            $model = new LanguageSerie(null, $languageId);
            $languageSerieModelListSaved = $model->getByLanguageId($languageId);

            if (!$languageSerieModelListSaved) {
                error_log("Database exception: ID del idioma no encontrado en la tabla language_serie de la base de datos - LANGUAGE_ID [{$languageId}]");
                return false;
            }

            foreach ($languageSerieModelListSaved as $languageSerieModelItem) {
                $languageSerieObjectItem = new LanguageSerie($languageSerieModelItem->getSerieId(), $languageSerieModelItem->getLanguageId(), $languageSerieModelItem->getAudio(), $languageSerieModelItem->getSubtitle());
                array_push($languageSerieObjectListSaved, $languageSerieObjectItem);
            }

            return $languageSerieObjectListSaved;
        } catch (InvalidArgumentException $e) {
            error_log("[DirectorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function showBySerieAndComponents($serieId, $audio, $subtitle): mixed
    {
        $this->checkValidIdDataType($serieId);

        $languageSerieObjectListSaved = [];
        $model = new LanguageSerie($serieId, null, $audio, $subtitle);

        $languageSerieModelListSaved = $model->getBySerieIdAndComponents();

        if (!$languageSerieModelListSaved) {
            error_log("Database exception: ID del idioma no encontrado en la tabla language_serie de la base de datos - SERIE_ID [{$serieId}] - AUDIO [{$audio}] - SUBTITLE [{$subtitle}]");
            return false;
        }

        foreach ($languageSerieModelListSaved as $languageSerieModelItem) {
            $languageSerieObjectItem = new LanguageSerie($languageSerieModelItem->getSerieId(), $languageSerieModelItem->getLanguageId(), $languageSerieModelItem->getAudio(), $languageSerieModelItem->getSubtitle());
            array_push($languageSerieObjectListSaved, $languageSerieObjectItem);
        }

        return $languageSerieObjectListSaved;
    }


    function create($serieId, $languages): bool
    {
        try {

            $this->checkValidIdDataType($serieId);
            $this->checkValidInputFields($languages);

            $model = new LanguageSerie($serieId);
            $languageSaved = $model->save($languages);

            if ($languageSaved) {
                return true;
            }

            $languageEncoded = json_encode($languages);
            error_log("[LanguageSerieController] [Data Error] Falló al guardar los idiomas disponibles de audio y subtitulos [{$languageEncoded}] de la serie [{$serieId}] en la tabla language_serie");
            throw new RuntimeException("Los idiomas disponibles de la serie [{$serieId}] no se han creado correctamente.", Constants::INTERNAL_SERVER_ERROR_CODE);
        } catch (InvalidArgumentException $e) {
            error_log("[LanguageSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[LanguageSerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    function edit($serieId, $newLanguages): bool
    {

        try {
            $this->checkValidIdDataType($serieId);
            $this->checkValidInputFields($newLanguages);
            $this->checkValidLanguage($newLanguages);

            $languageSerieSaved = $this->showBySerieId($serieId);

            $savedLanguageIds = [];

            foreach ($languageSerieSaved as $item) {
                $savedLanguageIds[] = $item->getLanguageId();
            }

            $this->saveNewSerieAvailableLanguages($serieId, $newLanguages, $savedLanguageIds);

            $model = new LanguageSerie($serieId);

            $languageSerieEdited = $model->update($newLanguages);

            if ($languageSerieEdited) {
                return true;
            }

            $directorIdsDecode = json_encode($newLanguages);
            error_log("[DirectorSerieController] [Data Error] Falló al actualizar los directores/as [{$directorIdsDecode}] de la serie [{$serieId}] en la tabla director_serie");
            throw new RuntimeException("Los/as directores/as [{$directorIdsDecode}] de la serie [{$serieId}] no se han editado correctamente.", Constants::INTERNAL_SERVER_ERROR_CODE);
        } catch (RecordNotFoundException $e) {
            error_log("[DirectorSerieController] [Record Not Found Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (InvalidArgumentException $e) {
            error_log("[DirectorSerieController] [Invalid Argument Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        } catch (RuntimeException $e) {
            error_log("[DirectorSerieController] [Runtime Exception] Code: " . $e->getCode() . " - Message: " . $e->getMessage());
            return false;
        }
    }

    private function saveNewSerieAvailableLanguages(int $serieId, array $newLanguagesIds, array $languagesIdsSaved): void
    {
        $languagesSaved = array_flip($languagesIdsSaved);

        $newLanguageSerie = array_diff_key($newLanguagesIds, $languagesSaved);

        if (!empty($newLanguageSerie)) {
            $this->create($serieId, $newLanguageSerie);
        }
    }

    function checkValidInputFields($languages): void
    {
        $inputInvalid = false;

        foreach ($languages as $languageIdItem => $languageComponents) {

            if (LanguageValidation::isInvalidIdDataType($languageIdItem)) {
                error_log("[LanguageSerieController] [Validation Error] ID del idioma inválido. Debe contener solo números. LANGUAGE_ID - [{$languageIdItem}]");
                $inputInvalid = true;
            }

            if (LanguageValidation::validateLanguageComponentsType($languageComponents)) {
                error_log("[LanguageSerieController] [Validation Error] Componentes del idioma audio y subtitulos vacios o no cumplen con el formato de ser un arreglo. LANGUAGE_ID [{$languageIdItem}] - COMPONENTS [{$languageComponents}]");
                $inputInvalid = true;
            }

            if (LanguageValidation::validateLanguageComponentsKey($languageComponents)) {
                $componentsEncode = json_encode($languageComponents);
                error_log("[LanguageSerieController] [Validation Error] Componentes del idioma audio y subtitulos no son válidos. LANGUAGE_ID [{$languageIdItem}] - COMPONENTS - [{$componentsEncode}]");
                $inputInvalid = true;
            }

            if (LanguageValidation::validateLanguageComponentsValue($languageComponents)) {
                $componentsEncode = json_encode($languageComponents);
                error_log("[LanguageSerieController] [Validation Error] Valores para los componentes del idioma audio y/o subtitulos no son validos. LANGUAGE_ID [{$languageIdItem}] - COMPONENTS - [{$componentsEncode}]");
                $inputInvalid = true;
            }

            if ($inputInvalid) {
                break;
            }
        }

        if ($inputInvalid) {
            throw new InvalidArgumentException("Por favor verificar la información ingresada.", Constants::BAD_REQUEST_CODE);
        }
    }


    private function checkValidIdDataType($id): void
    {
        if (SerieValidation::isInvalidIdDataType($id)) {
            error_log("[LanguageSerieController] [Validation Error] ID inválido. Debe contener solo números - [{$id}]");
            throw new InvalidArgumentException("Id [{$id}] inválido", Constants::BAD_REQUEST_CODE);
        }
    }

    private function checkValidLanguage($languageIdsData)
    {
        $languageController = new LanguageController();
        foreach ($languageIdsData as $languageKeyItem => $languageValueItem) {
            $languageSaved = $languageController->showById($languageKeyItem);
            if (!$languageSaved) {
                error_log("[LanguageSerieController] [Data Error] ID del idioma disponible no encontrado en la tabla language de la base de datos - [{$languageKeyItem}]");
                throw new RecordNotFoundException("Idioma [{$languageKeyItem}] no registrado/a", Constants::NOT_FOUND_CODE);
            }
        }
    }

    function getAudioLanguageSerieOptions(int $serieId): string
    {
        $audioLanguages = $this->showBySerieAndComponents($serieId, 1, null);

        return $this->retrieveOptionSelect($audioLanguages, true);
    }

    function getSubtitleLanguageSerieOptions(int $serieId): string
    {
        $subtitleLanguages = $this->showBySerieAndComponents($serieId, null, 1);

        return $this->retrieveOptionSelect($subtitleLanguages);
    }

    function retrieveOptionSelect($languages, $audioFlag = false): string
    {
        $options = '';

        $languageController = new LanguageController();

        $languageList = $languageController->showAll();

        $selectedLanguageIds = [];
        if (isset($languages) && !empty($languages) && !is_bool($languages)) {
            foreach ($languages as $languageItem) {
                $selectedLanguageIds[] = $languageItem->getLanguageId();
            }
        }

        //Remover opcion de No Aplica para idiomas de audio
        if ($audioFlag) {
            unset($languageList[0]);
        }

        foreach ($languageList as $languageItem) {
            $languageId = $languageItem->getId();
            $languageOption = Utilities::concatStrings("[", $languageItem->getIsoCode(), "]", " - ", $languageItem->getName() . " (" . $languageItem->getIsoCode() .")");

            if (in_array($languageId, $selectedLanguageIds)) {
                $options .= '<option value="' . $languageId . '" selected=' . $languageId . '>' . $languageOption . '</option>';
            } else {
                $options .= '<option value="' . $languageId . '">' . $languageOption . '</option>';
            }
        }

        return $options;
    }

    function checkActiveAvailableLanguageSeries($languageId): bool
    {
        $hasActiveAvailableLanguages = false;

        $languageSerieList = $this->showByLanguageId($languageId);

        if (isset($languageSerieList) && !empty($languageSerieList)) {

            foreach ($languageSerieList as $languageSerieItem) {
                if ($languageSerieItem->getAudio() == 1 || $languageSerieItem->getSubtitle() == 1) {
                    error_log("No se puede eliminar el idioma de la tabla language, tiene series activas en la tabla language_serie - LANGUAGE_ID: {$languageId}");
                    Utilities::setWarningMessage("El idioma tiene series asociadas");
                    $hasActiveAvailableLanguages = true;
                    break;
                }
            }
        }

        return $hasActiveAvailableLanguages;
    }

    /**
     * Obtiene el listado de series activas de un idioma.
     * @param int $languageId ID del idioma.
     * @return string Listado de series activas de un idioma.
     */
    function getSerieListByLanguage($languageId): string
    {
        $options = '';

        $withoutOrInactiveRecords = '<li class="list-group-item">No se encontraron series asociadas</li>';

        $languageSerieList = $this->showByLanguageId($languageId);

        $serieController = new SerieController();

        if (is_bool($languageSerieList) && !$languageSerieList) {

            $options .= $withoutOrInactiveRecords;
        } else {

            foreach ($languageSerieList as $languageSerieItem) {
                if ($languageSerieItem->getAudio() == 1 || $languageSerieItem->getSubtitle() == 1) {

                    $serie = $serieController->showById($languageSerieItem->getSerieId());

                    $serieLanguageOption = Utilities::concatStrings("[", $serie->getId(), "]", " - ", $serie->getTitle());

                    $options .= '<li class="list-group-item">' . $serieLanguageOption . '</li>';
                }
            }

            if ($options == '') {
                $options .= $withoutOrInactiveRecords;
            }
        }

        return $options;
    }
}

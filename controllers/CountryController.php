<?php
require_once '../../utils/SessionStart.php';
require_once '../../utils/Utilities.php';
require_once('../../models/Country.php');
class CountryController
{
    function showAll(): array
    {
        $model = new Country();
        $countryList = $model->getAll();
        $countryObjectArray = [];
        foreach ($countryList as $item) {
            $name = ucfirst(Utilities::convertCharacters(0, $item->getName()));
            $demonym = ucfirst(Utilities::convertCharacters(0, $item->getDemonym()));
            $countryObject = new Country($item->getId(), $name, $demonym, $item->getLanguageId());
            array_push($countryObjectArray, $countryObject);
        }
        return $countryObjectArray;
    }

    function showById($id): mixed
    {

        $model = new Country($id);
        $countrySaved = $model->getById();

        if (!$countrySaved) {
            error_log("Database exception: ID del pais no encontrado en la base de datos - [{$id}]");
            return false;
        }
        $country = new Country(
            $countrySaved->getId(), 
            ucfirst(Utilities::convertCharacters(0, $countrySaved->getName())),
            ucfirst(Utilities::convertCharacters(0, $countrySaved->getDemonym())), 
            $countrySaved->getLanguageId()
        );
        return $country;
    }

}

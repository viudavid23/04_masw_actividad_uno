<?php
require_once '../../controllers/LanguageSerieController.php';

if (isset($_POST['languageId'])) {
    $languageId = $_POST['languageId'];
    $languageSerieController = new LanguageSerieController();
    $languageseriesOptions = $languageSerieController->getSerieListByLanguage($languageId);
    echo $languageseriesOptions;
} else {
    echo '<li class="list-group-item">No se encontraron series</li>';
}

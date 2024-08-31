<?php
require_once '../../controllers/SerieController.php';

if (isset($_POST['directorId'])) {
    $directorId = $_POST['directorId'];
    $serieController = new SerieController();
    $seriesDirectorOptions = $serieController->getSeriesListByDirector($directorId);
    echo $seriesDirectorOptions;
} else {
    echo '<li class="list-group-item">No se encontraron series</li>';
}

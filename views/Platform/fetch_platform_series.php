<?php
require_once '../../controllers/SerieController.php';

if (isset($_POST['platformId'])) {
    $platformId = $_POST['platformId'];
    $serieController = new SerieController();
    $seriesPlatformOptions = $serieController->getSeriesListByPlatform($platformId);
    echo $seriesPlatformOptions;
} else {
    echo '<li class="list-group-item">No se encontraron series</li>';
}
?>
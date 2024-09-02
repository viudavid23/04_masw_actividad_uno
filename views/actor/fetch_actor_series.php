<?php
require_once '../../controllers/SerieController.php';

if (isset($_POST['actorId'])) {
    $actorId = $_POST['actorId'];
    $serieController = new SerieController();
    $seriesActorOptions = $serieController->getSeriesListByActor($actorId);
    echo $seriesActorOptions;
} else {
    echo '<li class="list-group-item">No se encontraron series</li>';
}

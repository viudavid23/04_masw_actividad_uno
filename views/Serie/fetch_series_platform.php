<?php
 require_once '../../controllers/PlatformSerieController.php';

if (isset($_POST['serieId'])) {
    $serieId = $_POST['serieId'];
    $platformSerieController = new PlatformSerieController();
    $seriesPlatformOptions = $platformSerieController->getPlatformListBySerie($serieId);
    echo $seriesPlatformOptions;
} else {
    echo '<li class="list-group-item">No se encontraron plataformas</li>';
}
?>
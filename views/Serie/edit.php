<!DOCTYPE html>
<?php
require_once '../../utils/SessionStart.php';
require_once '../../controllers/SerieController.php';
require_once('../../controllers/PlatformSerieController.php');
require_once('../../controllers/ActorSerieController.php');

$platformSerieController = new PlatformSerieController;
$actorSerieController = new ActorSerieController;
$serieController = new SerieController();

$sendData = false;
$errorOccurred = false;

$serieId = $_GET['id'];
$serieSaved = $serieController->showById($serieId);

if (!is_bool($serieSaved)) {
    $platformOptions = $platformSerieController->getPlatformOptions($serieId);
    $actorOptions = $actorSerieController->getActorSerieOptions($serieId);
} else {
    $errorOccurred = true;
}

if (isset($_POST['editBtn'])) {
    $sendData = true;
}
if ($sendData && !$errorOccurred) {

    $serieData = [
        'id' => $serieId,
        'title' => $_POST['title'],
        'synopsis' => $_POST['synopsis'],
        'release_date' => $_POST['releaseDate'],
        'platforms' => $_POST['platformsSelect'],
        'actors' => $_POST['actorsSelect']
    ];

    if (!is_bool($serieSaved)) {

        $serieEdited = $serieController->edit($serieId, $serieData);

        if ($serieEdited) {
            $serieSaved = $serieController->showById($serieId);
            $platformOptions = $platformSerieController->getPlatformOptions($serieId);
            $actorOptions = $actorSerieController->getActorSerieOptions($serieId);
        }
    }
}

?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Series</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>

<body>
    <div class="container">

        <?php include('../../menu.php'); ?>

        <form class="row g-3" name="create_platform" action="" method="POST">
            <h2 class="h2">Edicion de Series</h2>
            <?php

            if ($errorOccurred) {

                if (isset($_SESSION['error_message'])) {
            ?>
                    <div class="row">
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message'] ?> <br><a href="list.php">Volver al listado de Series</a>
                        </div>
                    </div>
                <?php
                    unset($_SESSION['error_message']);
                }

                if (isset($_SESSION['warning_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-warning" role="alert">
                            <?php echo $_SESSION['warning_message'] ?> <br><a href="list.php">Volver al listado de Series</a>
                        </div>
                    </div>
                <?php
                    unset($_SESSION['warning_message']);
                }
            } else {

                if (!$sendData) {
                ?>
                    <div class="row">
                        <div class="alert alert-info" role="alert">
                            Diligencie los campos para editar la serie.
                        </div>
                    </div>
                    <?php
                } else {

                    if (isset($_SESSION['error_message'])) {
                    ?>
                        <div class="row">
                            <div class="alert alert-danger" role="alert">
                                <?php echo $_SESSION['error_message'] ?> <br><a href="edit.php?id=<?php echo $serieId; ?>">Volver Intentarlo</a>
                            </div>
                        </div>
                    <?php
                        unset($_SESSION['error_message']);
                    }

                    if (isset($_SESSION['warning_message'])) {

                    ?>
                        <div class="row">
                            <div class="alert alert-warning" role="alert">
                                <?php echo $_SESSION['warning_message'] ?> <br><a href="edit.php?id=<?php echo $serieId; ?>">Volver Intentarlo</a>
                            </div>
                        </div>
                    <?php
                        unset($_SESSION['warning_message']);
                    }


                    if (isset($_SESSION['success_message'])) {
                    ?>
                        <div class="row">
                            <div class="alert alert-success" role="alert">
                                <?php echo $_SESSION['success_message'] ?>
                            </div>
                        </div>
                <?php
                        unset($_SESSION['success_message']);
                    }
                }
                ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Id:</label>
                        <input type="text" class="form-control" name="id" value="<?php echo $serieId ?>" disabled>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="titulo">Título <span class="required">*</span></label>
                        <input type="text" class="form-control" id="titulo" name="title" placeholder="Ingrese el título" value="<?php echo $serieSaved->getTitle(); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="fechaLanzamiento">Fecha de Lanzamiento</label>
                        <input type="date" class="form-control" id="fechaLanzamiento" name="releaseDate" value="<?php echo $serieSaved->getReleaseDate(); ?>">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="sinopsis">Sinópsis<span class="required">*</span></label>
                    <textarea class="form-control" id="sinopsis" name="synopsis" rows="3" placeholder="Máximo 1000 caracteres"><?php echo $serieSaved->getSynopsis(); ?></textarea>
                </div>


                <div class="col-md-3">
                    <div class="form-group">
                        <label for="plataforma">Plataformas<span class="required">*</span></label>

                        <select id="plataforma" name="platformsSelect[]" multiple required>
                            <option value="Seleccione...">Seleccione...</option>
                            <?php echo $platformOptions; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="actor">Actores/Actrices<span class="required">*</span></label>

                        <select id="actor" name="actorsSelect[]" multiple required>
                            <option value="Seleccione...">Seleccione...</option>
                            <?php echo $actorOptions; ?>
                        </select>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary" name="editBtn">Editar</button>
                    <a href="list.php" class="btn btn-danger">Cancelar</a>
                </div>
            <?php } ?>
        </form>
    </div>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/popper.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>

</body>

</html>
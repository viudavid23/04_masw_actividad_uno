<!DOCTYPE html>
<?php
require_once '../../utils/SessionStart.php';

$sendData = false;

if (isset($_POST['createBtn'])) {
    $sendData = true;
}
if ($sendData) {

    require_once '../../controllers/SerieController.php';

    $serieController = new SerieController();

    $serieData = [
        'title' => $_POST['title'],
        'synopsis' => $_POST['synopsis'],
        'release_date' => $_POST['releaseDate'],
        'platforms' => $_POST['platformsSelect'],
        'actors' => $_POST['actorsSelect'],
        'directors' => $_POST['directorsSelect']
    ];

    $serieController->create($serieData);
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

        <form class="row g-3" name="create_serie" action="" method="POST">
            <h2 class="h2">Registro de Series</h2>
            <?php
            if (!$sendData) {
            ?>
                <div class="row">
                    <div class="alert alert-info" role="alert">
                        Diligencie los campos para registrar una nueva Serie.
                    </div>
                </div>
                <?php
            } else {

                if (isset($_SESSION['error_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message'] ?> <br><a href="create.php">Volver Intentarlo</a>
                        </div>
                    </div>
                <?php
                    unset($_SESSION['error_message']);
                }

                if (isset($_SESSION['warning_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-warning" role="alert">
                            <?php echo $_SESSION['warning_message'] ?> <br><a href="create.php">Volver Intentarlo</a>
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
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="titulo">Título <span class="required">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="title" placeholder="Ingrese el título">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="fechaLanzamiento">Fecha de Lanzamiento<span class="required">*</span></label>
                    <input type="date" class="form-control" id="fechaLanzamiento" name="releaseDate">
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="sinopsis">Sinópsis<span class="required">*</span></label>
                <textarea class="form-control" id="sinopsis" name="synopsis" rows="3" placeholder="Máximo 1000 caracteres"></textarea>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-4">
                    <label for="plataformas">Plataformas <span class="required">*</span></label>
                    <select class="form-control" id="plataformas" name="platformsSelect[]" multiple required>
                        <option selected>Seleccione...</option>
                        <?php

                        require_once '../../controllers/PlatformController.php';

                        $platformController = new PlatformController();

                        $plarformList = $platformController->showAll();

                        foreach ($plarformList as $item) {
                            $platformOption = Utilities::concatStrings("[", $item->getid(), "]", " - " ,$item->getName());
                        ?>
                            <option <?php echo 'value="' . $item->getid() . '"' ?>><?php echo $platformOption?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-4">
                    <label for="actores">Actores/Actrices <span class="required">*</span></label>
                    <select class="form-control" id="actores" name="actorsSelect[]" multiple required>
                        <option selected>Seleccione...</option>
                        <?php

                        require_once '../../controllers/ActorController.php';

                        $actorController = new ActorController();

                        $actorList = $actorController->showAllActors();

                        foreach ($actorList as $item) {
                            $person = $actorController->showPersonById($item->getPersonId());
                            $actorOption = Utilities::concatStrings("[", $person->getId(), "]", " - " ,$person->getFirstName() , " ", $person->getLastName());
                        ?>
                            <option <?php echo 'value="' . $item->getid() . '"' ?>><?php echo $actorOption; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-4">
                    <label for="directores">Directores/as <span class="required">*</span></label>
                    <select class="form-control" id="directores" name="directorsSelect[]" multiple required>
                        <option selected>Seleccione...</option>
                        <?php

                        require_once '../../controllers/DirectorController.php';

                        $directorController = new DirectorController();

                        $directorList = $directorController->showAllDirectors();

                        foreach ($directorList as $item) {
                            $person = $directorController->showPersonById($item->getPersonId());
                            $directorOption = Utilities::concatStrings("[", $person->getId(), "]", " - " ,$person->getFirstName() , " ", $person->getLastName());
                        ?>
                            <option <?php echo 'value="' . $item->getid() . '"' ?>><?php echo $directorOption; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary" name="createBtn">Guardar</button>
                <a href="list.php" class="btn btn-danger">Cancelar</a>
            </div>
        </form>
    </div>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/popper.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>

</body>

</html>
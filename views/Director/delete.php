<!DOCTYPE html>
<?php require_once '../../utils/SessionStart.php'; ?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Directores</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <?php
        include '../../menu.php';
        require_once '../../controllers/DirectorController.php';
        require_once '../../controllers/DirectorSerieController.php';
        ?>
        <div class="col-12">
            <h2 class="h2">Eliminación de Directores/as</h2>
            <?php

            $id = $_POST['directorId'];

            $directorSerieController = new DirectorSerieController();
            $hasActiveSeries = $directorSerieController->checkActiveDirectorSeries($id);

            if(!$hasActiveSeries) {
                $directorController = new DirectorController();
                $directorController->deleteDirector($id);
            }
            
            if (isset($_SESSION['error_message'])) {
            ?>
                <div class="row">
                    <div class="alert alert-danger" role="alert">
                        <?php echo $_SESSION['error_message'] ?> <br><a class="btn btn-link" href="list.php">Volver a intentarlo</a>
                    </div>
                </div>
            <?php
                unset($_SESSION['error_message']);
            }

            if (isset($_SESSION['warning_message'])) {
            ?>
                <div class="row">
                    <div class="alert alert-warning" role="alert">
                        <?php echo $_SESSION['warning_message'] ?> <br><a class="btn btn-link" href="list.php">Volver a intentarlo</a>
                    </div>
                </div>
            <?php
                unset($_SESSION['warning_message']);
            }


            if (isset($_SESSION['success_message'])) {
            ?>
                <div class="row">
                    <div class="alert alert-success" role="alert">
                        <?php echo $_SESSION['success_message'] ?> <br><a class="btn btn-link" href="list.php">Volver al listado de Directores/as</a>
                    </div>
                </div>
            <?php
                unset($_SESSION['success_message']);
            }
            ?>
        </div>
    </div>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/popper.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
</body>

</html>
<!DOCTYPE html>
<?php
require_once '../../utils/SessionStart.php';

$sendData = false;

if (isset($_POST['createBtn'])) {
    $sendData = true;
}
if ($sendData) {

    require_once '../../controllers/PlatformController.php';

    $platformController = new PlatformController();

    $platformData = [
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'releaseDate' => $_POST['releaseDate'],
        'logo' => $_POST['logo'],
    ];

    $platformController->create($platformData);
}

?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Plataformas</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>

<body>
    <div class="container">

        <?php include('../../menu.php'); ?>

        <form class="row g-3" name="create_platform" action="" method="POST">
            <h2 class="h2">Registro de Plataformass</h2>
            <?php
            if (!$sendData) {
            ?>
                <div class="row">
                    <div class="alert alert-info" role="alert">
                        Diligencie los campos para registrar una nueva Plataforma.
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
                    <label for="nombre">Nombre <span class="required">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="name" placeholder="Ingrese el nombre">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="descripcion">Descripción</label>
                    <input type="text" class="form-control" id="descripcion" name="description" placeholder="Ingrese la descripcion">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="fechaLanzamiento">Fecha de Lanzamiento</label>
                    <input type="date" class="form-control" id="fechaLanzamiento" name="releaseDate">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="logotipo">Logo</label>
                    <input type="text" class="form-control" id="logotipo" name="logo" placeholder="Ingrese la URL del logo">
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
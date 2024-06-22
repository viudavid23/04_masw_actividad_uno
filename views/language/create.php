<!DOCTYPE html>
<?php

$sendData = false;

if (isset($_POST['createBtn'])) {
    $sendData = true;
}
if ($sendData) {

    require_once '../../controllers/LanguageController.php';

    $controller = new LanguageController();

    $controller->create($_POST['name'], $_POST['iso']);
}

?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Idiomas</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container">

    <form name="create_kind" action="" method="POST">
        <div class="btn-group" role="group" aria-label="Buttons Area">
            <a class="btn btn-link" href="list.php">Volver al listado de Idiomas</a>
        </div>

        <?php
        if (!$sendData) {
        ?>
            <div class="row">
                <div class="alert alert-info" role="alert">
                    Diligencie los campos para registrar un nuevo idioma.
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
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Nombre:</label>
                <input type="text" class="form-control" name="name" placeholder="Inglés" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Código ISO:</label>
                <input type="text" class="form-control" name="iso" placeholder="EN" required>
            </div>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" name="createBtn">Guardar</button>
            <a href="list.php" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
    <script src="assets/js/jquery-3.2.1.slim.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>
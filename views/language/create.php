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

<body>
    <div class="container">
        <?php include('../../menu.php'); ?>
        <form name="create_language" action="" method="POST">
            <h2 class="h2">Registro de Idiomas</h2>
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
                    <input type="text" class="form-control" name="name" placeholder="Inglés" pattern="[a-zA-ZÀ-ÿ\s]+" title="Nombre inválido." required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Código ISO:</label>
                    <input type="text" class="form-control" name="iso" placeholder="ISO 3166-1" pattern="[A-Z0-9]{2,3}" title="Código ISO inválido." required>
                </div>
            </div>
            <div>
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
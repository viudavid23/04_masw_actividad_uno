<!DOCTYPE html>
<?php
require_once '../../utils/SessionStart.php';
require_once '../../controllers/LanguageController.php';

$controller = new LanguageController();

$sendData = false;

$languageId = $_GET['id'];

$languageSaved = $controller->showById($languageId);

if (isset($_POST['editBtn'])) {
    $sendData = true;
}
if ($sendData) {

    $languageEdited = $controller->edit($languageId, $_POST['name'], $_POST['iso']);

    if ($languageEdited) {
        $languageSaved = $controller->showById($languageId);
    }
}

?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Idiomas</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>

<body>
    <div class="container">

        <?php include('../../menu.php'); ?>

        <form name="edit_language" action="" method="POST">
            <h2 class="h2">Edición de Idiomas</h2>
            <?php

            if (is_bool($languageSaved)) {

                if (isset($_SESSION['error_message'])) {
            ?>
                    <div class="row">
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message'] ?> <br><a href="list.php">Volver al listado de Idiomas</a>
                        </div>
                    </div>
                <?php
                    unset($_SESSION['error_message']);
                }
                exit;
            }

            if (!$sendData) {
                ?>
                <div class="row">
                    <div class="alert alert-info" role="alert">
                        Actualice los campos para editar el idioma.
                    </div>
                </div>
                <?php
            } else {

                if (isset($_SESSION['error_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message'] ?> <br><a href="edit.php?id=<?php echo $languageId; ?>">Volver Intentarlo</a>
                        </div>
                    </div>
                <?php
                    unset($_SESSION['error_message']);
                }

                if (isset($_SESSION['warning_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-warning" role="alert">
                            <?php echo $_SESSION['warning_message'] ?> <br><a href="edit.php?id=<?php echo $languageId; ?>">Volver Intentarlo</a>
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
                    <label class="form-label">Id:</label>
                    <input type="text" class="form-control" name="id" value="<?php echo $languageId ?>" disabled>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Nombre:</label>
                    <input type="text" class="form-control" name="name" placeholder="Inglés" pattern="[a-zA-ZÀ-ÿ\s]+" title="Nombre inválido." value="<?php echo $languageSaved->getName(); ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Código ISO:</label>
                    <input type="text" class="form-control" name="iso" placeholder="ISO 3166-1" pattern="[a-zA-Z0-9]{2,3}" title="Código ISO inválido." value="<?php echo $languageSaved->getIsoCode(); ?>" required>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" name="editBtn">Editar</button>
                <a href="list.php" class="btn btn-danger">Cancelar</a>
            </div>
        </form>
    </div>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/popper.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
</body>

</html>
<!DOCTYPE html>
<?php
require_once('../../controllers/LanguageController.php')
?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Idiomas</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="col-12">
        <h2 class="h2">Idiomas</h2>
        <div class="btn-group mb-3" role="group" aria-label="Buttons Area">
            <a class="btn btn-primary" href="create.php">Registrar</a>
        </div>
        <?php
        $languageController = new LanguageController();

        $languageList = $languageController->showAll();

        if (count($languageList) > 0) {

        ?>

            <table class="table">
                <thead>
                    <th>Id</th>
                    <th>Nombre</th>
                    <th>Código ISO</th>
                    <th></th>
                </thead>
                <tbody>
                    <?php
                    foreach ($languageList as $item) {
                    ?>
                        <tr>
                            <td><?php echo $item->getId() ?></td>
                            <td><?php echo $item->getName() ?></td>
                            <td><?php echo $item->getIsoCode() ?></td>

                            <td>
                                <div class="btn-group" role="group" aria-label="Buttons Area">
                                    <a class="btn btn-success" href="edit.php?languageId=<?php echo $item->getId(); ?>&edited=false">Editar</a>
                                </div>
                                <div class="btn-group" role="group" aria-label="Buttons Area">
                                    <form name="delete_language" action="delete.php" method="POST">
                                        <input type="hidden" name="languageId" value="<?php echo $item->getId(); ?>" />
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        <?php
        } else {
        ?>
            <div class="alert alert-warning" role="alert">
                Aún no existen registros de idiomas.
            </div>
        <?php
        }
        ?>
    </div>
    <script src="../../assets/js/jquery-3.2.1.slim.min.js"></script>
    <script src="../../assets/js/popper.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
</body>

</html>
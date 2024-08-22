<!DOCTYPE html>
<?php require_once '../../utils/SessionStart.php'; ?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Idiomas</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">

        <?php include '../../menu.php'; ?>

        <div class="col-12">
            <h2 class="h2">Listado de Idiomas</h2>
            <div class="btn-group mb-3" role="group" aria-label="Buttons Area">
                <a class="btn btn-primary" href="create.php">Registrar</a>
            </div>
            <?php

            require_once '../../controllers/LanguageController.php';

            $languageController = new LanguageController();

            $languageList = $languageController->showAll();

            if (count($languageList) > 0) {

            ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Código ISO</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($languageList as $item) {
                                $uniqueModalId = "deleteLanguageModal" . $item->getId();
                            ?>
                                <tr>
                                    <td><?php echo $item->getId() ?></td>
                                    <td><?php echo $item->getName() ?></td>
                                    <td><?php echo $item->getIsoCode() ?></td>

                                    <td>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <a class="btn btn-success" href="edit.php?id=<?php echo $item->getId(); ?>">Editar</a>
                                        </div>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#<?php echo $uniqueModalId; ?>">Eliminar</button>
                                            <!-- Modal Confirmación-->
                                            <div class="modal fade" id="<?php echo $uniqueModalId; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalTextLabel">Eliminación de Idioma <strong><?php echo $item->getName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Está acción eliminará el idioma y sus relaciones con las series.
                                                            ¿Desea continuar?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                                            <form name="delete_language" action="delete.php" method="POST">
                                                                <input type="hidden" name="languageId" value="<?php echo $item->getId(); ?>" />
                                                                <button type="submit" class="btn btn-primary">Confirmar</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
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
    </div>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/popper.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
</body>

</html>
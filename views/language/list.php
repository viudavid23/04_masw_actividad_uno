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
                                $languageSeriesModalId = "languageSerieModal" . $item->getId();
                            ?>
                                <tr>
                                    <td><?php echo $item->getId() ?></td>
                                    <td><?php echo $item->getName() ?></td>
                                    <td><?php echo $item->getIsoCode() ?></td>
                                    <?php
                                    if ($item->getId() != 1) {
                                    ?>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Buttons Area">
                                                <a class="btn btn-success" href="edit.php?id=<?php echo $item->getId(); ?>">Editar</a>
                                            </div>
                                            <div class="btn-group" role="group" aria-label="Buttons Area">

                                                <!-- Modal Confirmación-->
                                                <div class="modal fade" id="<?php echo $uniqueModalId; ?>" aria-hidden="true" aria-labelledby="staticBackdropLabel" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
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
                                                                <button class="btn btn-info" data-language-id="<?php echo $item->getId(); ?>" data-bs-target="#<?php echo $languageSeriesModalId; ?>" data-bs-toggle="modal" data-bs-dismiss="modal">Series Asociadas</button>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Segundo Modal para Series Asociadas -->
                                                <div class="modal fade" id="<?php echo $languageSeriesModalId; ?>" aria-hidden="true" aria-labelledby="languageSeriesLabel" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="languageSeriesLabel">Series en Idioma <strong><?php echo $item->getName(); ?></strong></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <ul class="list-group list-group-flush" id="series-list-<?php echo $item->getId(); ?>">
                                                                    <!-- El contenido se actualizará mediante AJAX -->
                                                                </ul>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button class="btn btn-primary" data-bs-target="#<?php echo $uniqueModalId; ?>" data-bs-toggle="modal" data-bs-dismiss="modal">Regresar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a class="btn btn-danger" data-bs-toggle="modal" href="#<?php echo $uniqueModalId; ?>" role="button">Eliminar</a>
                                            </div>
                                        </td>
                                    <?php
                                    }
                                    ?>
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
    <script>
        $(document).ready(function() {
            $('.btn-info').on('click', function() {
                var languageId = $(this).data('language-id'); // Obtener el ID de la plataforma desde el botón

                $.ajax({
                    url: 'fetch_language_series.php', // Este archivo PHP manejará la solicitud
                    method: 'POST',
                    data: {
                        languageId: languageId
                    },
                    success: function(response) {
                        $('#series-list-' + languageId).html(response); // Llenar la lista con la respuesta del servidor
                        var modalId = '#languageSerieModal' + languageId;
                        $('modalId').modal('show'); // Mostrar el segundo modal después de cargar los datos
                    }
                });
            });
        });
    </script>
</body>

</html>
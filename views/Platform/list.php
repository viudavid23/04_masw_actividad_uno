<!DOCTYPE html>
<?php require_once '../../utils/SessionStart.php'; ?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Plataformas</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">

        <?php include('../../menu.php'); ?>

        <div class="col-12">
            <h2 class="h2">Listado de Plataformas</h2>
            <div class="btn-group mb-3" role="group" aria-label="Buttons Area">
                <a class="btn btn-primary" href="create.php">Registrar</a>
            </div>
            <?php

            require_once '../../controllers/PlatformController.php';

            $platformController = new PlatformController();

            $platformList = $platformController->showAll();

            if (count($platformList) > 0) {

            ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Fecha de Lanzamiento</th>
                            <th>Logo</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($platformList as $platform) {

                                $platform = $platformController->showById($platform->getId());
                                $platform->setReleaseDate(Utilities::changeDateFormat($platform->getReleaseDate(), Constants::DATE_OUTPUT_FORMAT));
                                $uniqueModalId = "deletePlatformModal" . $platform->getId();
                                $platformsModalId = "seriesPlatformModal" . $platform->getId();
                            ?>
                                <tr>
                                    <td><?php echo $platform->getId(); ?></td>
                                    <td><?php echo $platform->getName(); ?></td>
                                    <td><?php echo $platform->getDescription(); ?></td>
                                    <td><?php echo $platform->getReleaseDate(); ?></td>
                                    <td><?php echo $platform->getLogo(); ?></td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <a class="btn btn-success" href="edit.php?id=<?php echo $platform->getId(); ?>">Editar</a>
                                        </div>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">

                                            <!-- Modal Confirmación-->
                                            <div class="modal fade" id="<?php echo $uniqueModalId; ?>" aria-hidden="true" aria-labelledby="staticBackdropLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalTextLabel">Eliminación de Plataforma <strong><?php echo $platform->getName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Está acción eliminará la plataforma. Para proceder, no debe tener series asociadas.
                                                            ¿Desea continuar?
                                                        </div>

                                                        <div class="modal-footer">

                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                                            <form name="delete_platform" action="delete.php" method="POST">
                                                                <input type="hidden" name="platformId" value="<?php echo $platform->getId(); ?>" />
                                                                <button type="submit" class="btn btn-primary">Confirmar</button>
                                                            </form>

                                                            <button class="btn btn-info" data-platform-id="<?php echo $platform->getId(); ?>" data-bs-target="#<?php echo $platformsModalId; ?>" data-bs-toggle="modal" data-bs-dismiss="modal">Series Asociadas</button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Segundo Modal para Series Asociadas -->
                                            <div class="modal fade" id="<?php echo $platformsModalId; ?>" aria-hidden="true" aria-labelledby="platformSeriesLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="platformSeriesLabel">Series Asociadas para <strong><?php echo $platform->getName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <ul class="list-group list-group-flush" id="series-list-<?php echo $platform->getId(); ?>">
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
                    Aún no existen registros de plataformas.
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
                var platformId = $(this).data('platform-id'); // Obtener el ID de la plataforma desde el botón

                $.ajax({
                    url: 'fetch_platform_series.php', // Este archivo PHP manejará la solicitud
                    method: 'POST',
                    data: {
                        platformId: platformId
                    },
                    success: function(response) {
                        $('#series-list-' + platformId).html(response); // Llenar la lista con la respuesta del servidor
                        var modalId = '#seriesPlatformModal' + platformId;
                        $('modalId').modal('show'); // Mostrar el segundo modal después de cargar los datos
                    }
                });
            });
        });
    </script>
</body>

</html>
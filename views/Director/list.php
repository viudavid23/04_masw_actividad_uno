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
        
    <?php include('../../menu.php'); ?>
        
        <div class="col-12">
            <h2 class="h2">Listado de Directores/as</h2>
            <div class="btn-group mb-3" role="group" aria-label="Buttons Area">
                <a class="btn btn-primary" href="create.php">Registrar</a>
            </div>
            <?php

            require_once '../../controllers/DirectorController.php';

            $directorController = new DirectorController();

            $directorList= $directorController->showAllDirectors();

            if (count($directorList) > 0) {

                require_once '../../controllers/CountryController.php';

                $countryController = new CountryController();
            ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Fecha Nacimiento</th>
                            <th>Nacionalidad</th>
                            <th>Inicio de Carrera</th>
                            <th>Años Activo</th>
                            <th>Biografía</th>
                            <th>Premios</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($directorList as $director) {

                                $person = $directorController->showPersonById($director->getPersonId());
                                $person->setBirthdate(Utilities::changeDateFormat($person->getBirthdate(), Constants::DATE_OUTPUT_FORMAT));
                                $director->setBeginningCareer(Utilities::changeDateFormat($director->getBeginningCareer(), Constants::DATE_OUTPUT_FORMAT));
                                $country = $countryController->showById($person->getCountryId());
                                $uniqueModalId = "deleteDirectorModal" . $director->getId();
                                $directorsModalId = "seriesDirectorModal" . $director->getId();
                            ?>
                                <tr>
                                    <td><?php echo $director->getId(); ?></td>
                                    <td><?php echo $person->getFirstName(); ?></td>
                                    <td><?php echo $person->getLastName(); ?></td>
                                    <td><?php echo $person->getBirthdate(); ?></td>
                                    <td><?php echo $country->getDemonym(); ?></td>
                                    <td><?php echo $director->getBeginningCareer(); ?></td>
                                    <td><?php echo $director->getActiveYears(); ?></td>
                                    <td><?php echo $director->getBiography(); ?></td>
                                    <td><?php echo $director->getAwards(); ?></td>

                                    <td>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <a class="btn btn-success" href="edit.php?id=<?php echo $director->getId(); ?>">Editar</a>
                                        </div>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <!-- Modal Confirmación-->
                                            <div class="modal fade" id="<?php echo $uniqueModalId; ?>" aria-hidden="true" aria-labelledby="staticBackdropLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalTextLabel">Eliminación de Director/a <strong><?php echo $person->getFirstName() . " " . $person->getLastName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Está acción eliminará el/la director/a. Para proceder, no debe tener series asociadas.
                                                            ¿Desea continuar?
                                                        </div>

                                                        <div class="modal-footer">

                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                                            <form name="delete_director" action="delete.php" method="POST">
                                                                <input type="hidden" name="directorId" value="<?php echo $director->getId(); ?>" />
                                                                <button type="submit" class="btn btn-primary">Confirmar</button>
                                                            </form>

                                                            <button class="btn btn-info" data-director-id="<?php echo $director->getId(); ?>" data-bs-target="#<?php echo $directorsModalId; ?>" data-bs-toggle="modal" data-bs-dismiss="modal">Series Asociadas</button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Segundo Modal para Series Asociadas -->
                                            <div class="modal fade" id="<?php echo $directorsModalId; ?>" aria-hidden="true" aria-labelledby="directorSeriesLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="directorSeriesLabel">Series del director/a <strong><?php echo $person->getFirstName() . " " . $person->getLastName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <ul class="list-group list-group-flush" id="series-list-<?php echo $director->getId(); ?>">
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
                    Aún no existen registros de directores.
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
                var directorId = $(this).data('director-id'); // Obtener el ID de la plataforma desde el botón

                $.ajax({
                    url: 'fetch_director_series.php', // Este archivo PHP manejará la solicitud
                    method: 'POST',
                    data: {
                        directorId: directorId
                    },
                    success: function(response) {
                        $('#series-list-' + directorId).html(response); // Llenar la lista con la respuesta del servidor
                        var modalId = '#seriesDirectorModal' + directorId;
                        $('modalId').modal('show'); // Mostrar el segundo modal después de cargar los datos
                    }
                });
            });
        });
    </script>
</body>

</html>
<!DOCTYPE html>
<?php require_once '../../utils/SessionStart.php'; ?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Actores</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        
    <?php include('../../menu.php'); ?>
        
        <div class="col-12">
            <h2 class="h2">Listado de Actores/Actrices</h2>
            <div class="btn-group mb-3" role="group" aria-label="Buttons Area">
                <a class="btn btn-primary" href="create.php">Registrar</a>
            </div>
            <?php

            require_once '../../controllers/ActorController.php';

            $actorController = new ActorController();

            $actorList = $actorController->showAllActors();

            if (count($actorList) > 0) {

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
                            <th>Nombre Artístico</th>
                            <th>Talla</th>
                            <th>Biografía</th>
                            <th>Premios</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($actorList as $actor) {

                                $person = $actorController->showPersonById($actor->getPersonId());
                                $person->setBirthdate(Utilities::changeDateFormat($person->getBirthdate(), Constants::DATE_OUTPUT_FORMAT));
                                $country = $countryController->showById($person->getCountryId());
                                $uniqueModalId = "deletePlatformModal" . $actor->getId();
                                $seriesModalId = "seriesPlatformModal" . $actor->getId();
                            ?>
                                <tr>
                                    <td><?php echo $actor->getId(); ?></td>
                                    <td><?php echo $person->getFirstName(); ?></td>
                                    <td><?php echo $person->getLastName(); ?></td>
                                    <td><?php echo $person->getBirthdate(); ?></td>
                                    <td><?php echo $country->getDemonym(); ?></td>
                                    <td><?php echo $actor->getStageName(); ?></td>
                                    <td><?php echo $actor->getHeight(); ?></td>
                                    <td><?php echo $actor->getBiography(); ?></td>
                                    <td><?php echo $actor->getAwards(); ?></td>

                                    <td>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <a class="btn btn-success" href="edit.php?id=<?php echo $actor->getId(); ?>">Editar</a>
                                        </div>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <!-- Modal Confirmación-->
                                            <div class="modal fade" id="<?php echo $uniqueModalId; ?>" aria-hidden="true" aria-labelledby="staticBackdropLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalTextLabel">Eliminación de Actor/Actriz <strong><?php echo $actor->getStageName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Está acción eliminará el actor/actriz. Para proceder, no debe tener series asociadas.
                                                            ¿Desea continuar?
                                                        </div>

                                                        <div class="modal-footer">

                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                                            <form name="delete_actor" action="delete.php" method="POST">
                                                                <input type="hidden" name="actorId" value="<?php echo $actor->getId(); ?>" />
                                                                <button type="submit" class="btn btn-primary">Confirmar</button>
                                                            </form>

                                                            <button class="btn btn-info" data-bs-target="#<?php echo $seriesModalId; ?>" data-bs-toggle="modal" data-bs-dismiss="modal">Series Asociadas</button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="<?php echo $seriesModalId; ?>" aria-hidden="true" aria-labelledby="actorSeriesLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="actorSeriesLabel">Series del actor/actriz <strong><?php echo $actor->getStageName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <ul class="list-group list-group-flush">
                                                                <?php 
                                                                    require_once '../../controllers/SerieController.php';

                                                                    $serieController = new SerieController();
                                                                    $seriesActorOptions = $serieController->getSeriesListByActor($actor->getId());
                                                                    
                                                                    echo $seriesActorOptions;
                                                                ?>
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
                    Aún no existen registros de actores.
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
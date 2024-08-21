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
                                $seriesModalId = "seriesPlatformModal" . $platform->getId();
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
                                                            Está acción eliminará la plataforma y la relación se las series que se encuentren asociadas.
                                                            ¿Desea continuar?
                                                        </div>

                                                        <div class="modal-footer">

                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                                            <form name="delete_platform" action="delete.php" method="POST">
                                                                <input type="hidden" name="platformId" value="<?php echo $platform->getId(); ?>" />
                                                                <button type="submit" class="btn btn-primary">Confirmar</button>
                                                            </form>

                                                            <button class="btn btn-info" data-bs-target="#<?php echo $seriesModalId; ?>" data-bs-toggle="modal" data-bs-dismiss="modal">Series Asociadas</button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="<?php echo $seriesModalId; ?>" aria-hidden="true" aria-labelledby="platformSeriesLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="platformSeriesLabel">Series de la plataforma <strong><?php echo $platform->getName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <ul class="list-group list-group-flush">
                                                                <?php 
                                                                    require_once '../../controllers/SerieController.php';

                                                                    $serieController = new SerieController();
                                                                    $seriesPlatformOptions = $serieController->getSeriesListByPlatform($platform->getId());
                                                                    
                                                                    echo $seriesPlatformOptions;
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
</body>

</html>
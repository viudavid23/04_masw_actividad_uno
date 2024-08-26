<!DOCTYPE html>
<?php require_once '../../utils/SessionStart.php'; ?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Series</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">

        <?php include('../../menu.php'); ?>

        <div class="col-12">
            <h2 class="h2">Listado de Series</h2>
            <div class="btn-group mb-3" role="group" aria-label="Buttons Area">
                <a class="btn btn-primary" href="create.php">Registrar</a>
            </div>
            <?php

            require_once '../../controllers/SerieController.php';

            $serieController = new SerieController();

            $serieList = $serieController->showAll();

            if (count($serieList) > 0) {

            ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <th>Id</th>
                            <th>Título</th>
                            <th>Sinópsis</th>
                            <th>Fecha de Lanzamiento</th>
                            <th>Detalle</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($serieList as $serie) {

                                $serie = $serieController->showById($serie->getId());
                                $serie->setReleaseDate(Utilities::changeDateFormat($serie->getReleaseDate(), Constants::DATE_OUTPUT_FORMAT));
                                $uniqueModalId = "deleteSerieModal" . $serie->getId();
                                $platformsModalId = "seriesPlatformModal" . $serie->getId();
                            ?>
                                <tr>
                                    <td><?php echo $serie->getId(); ?></td>
                                    <td><?php echo $serie->getTitle(); ?></td>
                                    <td><?php echo $serie->getSynopsis(); ?></td>
                                    <td><?php echo $serie->getReleaseDate(); ?></td>
                                    <td><a href="edit.php?id=<?php echo $serie->getId(); ?>"><?php echo $serie->getId(); ?></a></td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <a class="btn btn-success" href="edit.php?id=<?php echo $serie->getId(); ?>">Editar</a>
                                        </div>
                                        <div class="btn-group" role="group" aria-label="Buttons Area">
                                            <!-- Modal Confirmación-->
                                            <div class="modal fade" id="<?php echo $uniqueModalId; ?>" aria-hidden="true" aria-labelledby="staticBackdropLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalTextLabel">Eliminación de Serie <strong><?php echo $serie->getTitle(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Está acción eliminará la serie de todas las plataformas junto con sus actores/actrices e idiomas de audio y subtitulos.
                                                            ¿Desea continuar?
                                                        </div>

                                                        <div class="modal-footer">

                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                                            <form name="delete_serie" action="delete.php" method="POST">
                                                                <input type="hidden" name="serieId" value="<?php echo $serie->getId(); ?>" />
                                                                <button type="submit" class="btn btn-primary">Confirmar</button>
                                                            </form>
                                                            <button class="btn btn-info" data-bs-target="#<?php echo $platformsModalId; ?>" data-bs-toggle="modal" data-bs-dismiss="modal">Plataformas Asociadas</button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="<?php echo $platformsModalId; ?>" aria-hidden="true" aria-labelledby="platformSeriesLabel" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="platformSeriesLabel">Plataformas de la serie <strong><?php echo $serie->getTitle(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <ul class="list-group list-group-flush">
                                                                <?php
                                                                require_once '../../controllers/PlatformSerieController.php';

                                                                $platformSerieController = new PlatformSerieController();
                                                                $seriesPlatformOptions = $platformSerieController->getPlatformListBySerie($serie->getId());

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
                    Aún no existen registros de series.
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
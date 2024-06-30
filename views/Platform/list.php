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
                                            <form name="delete_platform" action="delete.php" method="POST">
                                                <input type="hidden" name="platformId" value="<?php echo $platform->getId(); ?>" />
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
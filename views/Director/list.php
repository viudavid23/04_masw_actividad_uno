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

                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#<?php echo $uniqueModalId; ?>">Eliminar</button>
                                            <!-- Modal Confirmación-->
                                            <div class="modal fade" id="<?php echo $uniqueModalId; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalTextLabel">Eliminación de Director/a <strong><?php echo $person->getFirstName() . " " . $person->getLastName(); ?></strong></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Está acción eliminará los/as directores/as de todas las series.
                                                            ¿Desea continuar?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                                            <form name="delete_director" action="delete.php" method="POST">
                                                                <input type="hidden" name="serieId" value="<?php echo $director->getId(); ?>" />
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
</body>

</html>
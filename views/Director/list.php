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
                                            <form name="delete_actor" action="delete.php" method="POST">
                                                <input type="hidden" name="actorId" value="<?php echo $director->getId(); ?>" />
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
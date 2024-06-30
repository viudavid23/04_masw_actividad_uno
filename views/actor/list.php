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
            <h2 class="h2">Listado de Actores</h2>
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
                                            <form name="delete_actor" action="delete.php" method="POST">
                                                <input type="hidden" name="actorId" value="<?php echo $actor->getId(); ?>" />
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
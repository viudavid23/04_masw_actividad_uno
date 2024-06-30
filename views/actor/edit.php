<!DOCTYPE html>
<?php
require_once '../../utils/SessionStart.php';

require_once '../../controllers/ActorController.php';

$actorController = new ActorController();

$sendData = false;

$actorId = $_GET['id'];

$actorSaved = $actorController->showActorById($actorId);

if (isset($_POST['editBtn'])) {
    $sendData = true;
}
if ($sendData) {

    $actorData = [
        'stageName' => $_POST['stageName'],
        'biography' => $_POST['biography'],
        'awards' => $_POST['awards'],
        'height' => $_POST['height'],
        'personId' => $actorSaved->getPersonId()
    ];

    $personData = [
        'id' => $actorSaved->getPersonId(),
        'firstName' => $_POST['firstName'],
        'lastName' => $_POST['lastName'],
        'birthdate' => $_POST['birthdate'],
        'countryId' => $_POST['nationality']
    ];

    $actorEdited = $actorController->editActor($actorSaved->getId(), $actorData, $personData);

    if ($actorEdited) {
        $actorSaved = $actorController->showActorById($actorId);
    }
}

?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Actores</title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/styles.css" rel="stylesheet">
</head>

<body>
    <div class="container">

        <?php include('../../menu.php'); ?>

        <form class="row g-3" name="create_language" action="" method="POST">
            <h2 class="h2">Edicion de Actores</h2>
            <?php

            if (is_bool($actorSaved)) {

                if (isset($_SESSION['error_message'])) {
            ?>
                    <div class="row">
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message'] ?> <br><a href="list.php">Volver al listado de Actores</a>
                        </div>
                    </div>
                    <?php
                    unset($_SESSION['error_message']);
                }
                exit;
            } else {
                $personSaved = $actorController->showPersonById($actorSaved->getPersonId());

                if (!$personSaved) {

                    if (isset($_SESSION['error_message'])) {
                    ?>
                        <div class="row">
                            <div class="alert alert-danger" role="alert">
                                <?php echo $_SESSION['error_message'] ?>
                            </div>
                        </div>
                <?php
                        unset($_SESSION['error_message']);
                    }
                    exit;
                }
            }

            if (!$sendData) {
                ?>
                <div class="row">
                    <div class="alert alert-info" role="alert">
                        Diligencie los campos para editar el/a actor/actriz.
                    </div>
                </div>
                <?php
            } else {

                if (isset($_SESSION['error_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message'] ?> <br><a href="edit.php?id=<?php echo $actorId; ?>">Volver Intentarlo</a>
                        </div>
                    </div>
                <?php
                    unset($_SESSION['error_message']);
                }

                if (isset($_SESSION['warning_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-warning" role="alert">
                            <?php echo $_SESSION['warning_message'] ?> <br><a href="edit.php?id=<?php echo $actorId; ?>">Volver Intentarlo</a>
                        </div>
                    </div>
                <?php
                    unset($_SESSION['warning_message']);
                }


                if (isset($_SESSION['success_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-success" role="alert">
                            <?php echo $_SESSION['success_message'] ?>
                        </div>
                    </div>
            <?php
                    unset($_SESSION['success_message']);
                }
            }
            ?>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Id:</label>
                    <input type="text" class="form-control" name="id" value="<?php echo $actorId ?>" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="primerNombre">Primer Nombre <span class="required">*</span></label>
                    <input type="text" class="form-control" id="primerNombre" name="firstName" placeholder="Ingrese el primer nombre" value="<?php echo $personSaved->getFirstName(); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="primerApellido">Primer Apellido <span class="required">*</span></label>
                    <input type="text" class="form-control" id="primerApellido" name="lastName" placeholder="Ingrese el primer apellido" value="<?php echo $personSaved->getLastName(); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="fechaNacimiento">Fecha de Nacimiento <span class="required">*</span></label>
                    <input type="date" class="form-control" id="fechaNacimiento" name="birthdate" value="<?php echo $personSaved->getBirthdate(); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-4">
                    <label for="nacionalidades">Nacionalidad <span class="required">*</span></label>
                    <select class="form-control" id="nacionalidades" name="nationality">
                        <option>Seleccione...</option>
                        <?php

                        require_once '../../controllers/CountryController.php';

                        $countryController = new CountryController();

                        $countryList = $countryController->showAll();

                        foreach ($countryList as $item) {

                            if ($item->getid() == $personSaved->getCountryId()) {
                                echo "<option selected value='" . $item->getid() . "'>" . $item->getDemonym() . "</option>";
                            } else {
                                echo "<option value='" . $item->getid() . "'>" . $item->getDemonym() . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="nombreArtistico">Nombre Artístico</label>
                    <input type="text" class="form-control" id="nombreArtistico" name="stageName" placeholder="Ingrese el nombre artístico" value="<?php echo $actorSaved->getStageName(); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="talla">Talla<span class="required">*</span></label>
                    <input type="number" class="form-control" id="talla" name="height" step="0.1" placeholder="Ingrese la talla" value="<?php echo $actorSaved->getHeight(); ?>">
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="premios">Biografia<span class="required">*</span></label>
                <textarea class="form-control" id="biografia" name="biography" rows="3" placeholder="Máximo 5000 caracteres"><?php echo $actorSaved->getBiography(); ?></textarea>
            </div>
            <div class="form-group mb-3">
                <label for="premios">Premios</span></label>
                <textarea class="form-control" id="premios" name="awards" rows="3"><?php echo $actorSaved->getAwards(); ?></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary" name="editBtn">Editar</button>
                <a href="list.php" class="btn btn-danger">Cancelar</a>
            </div>
        </form>
    </div>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/popper.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>

</body>

</html>
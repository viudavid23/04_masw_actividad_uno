<!DOCTYPE html>
<?php
require_once '../../utils/SessionStart.php';

$sendData = false;

if (isset($_POST['createBtn'])) {
    $sendData = true;
}
if ($sendData) {

    require_once '../../controllers/ActorController.php';

    $actorController = new ActorController();

    $actorData = [
        'stageName' => $_POST['stageName'],
        'biography' => $_POST['biography'],
        'awards' => $_POST['awards'],
        'height' => $_POST['height'],
    ];

    $personData = [
        'firstName' => $_POST['firstName'],
        'lastName' => $_POST['lastName'],
        'birthdate' => $_POST['birthdate'],
        'countryId' => $_POST['nationality']
    ];

    $actorController->createActor($actorData, $personData);
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

        <form class="row g-3" name="create_actor" action="" method="POST">
            <h2 class="h2">Registro de Actores/Actrices</h2>
            <?php
            if (!$sendData) {
            ?>
                <div class="row">
                    <div class="alert alert-info" role="alert">
                        Diligencie los campos para registrar un/a nuevo/a actor/actriz.
                    </div>
                </div>
                <?php
            } else {

                if (isset($_SESSION['error_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message'] ?> <br><a href="create.php">Volver Intentarlo</a>
                        </div>
                    </div>
                <?php
                    unset($_SESSION['error_message']);
                }

                if (isset($_SESSION['warning_message'])) {
                ?>
                    <div class="row">
                        <div class="alert alert-warning" role="alert">
                            <?php echo $_SESSION['warning_message'] ?> <br><a href="create.php">Volver Intentarlo</a>
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
            <div class="row">
                <h5>Datos Personales</h5>
                <hr>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="primerNombre">Primer Nombre <span class="required">*</span></label>
                    <input type="text" class="form-control" id="primerNombre" name="firstName" placeholder="Ingrese el primer nombre">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="primerApellido">Primer Apellido <span class="required">*</span></label>
                    <input type="text" class="form-control" id="primerApellido" name="lastName" placeholder="Ingrese el primer apellido">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="fechaNacimiento">Fecha de Nacimiento <span class="required">*</span></label>
                    <input type="date" class="form-control" id="fechaNacimiento" name="birthdate">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-4">
                    <label for="nacionalidades">Nacionalidad <span class="required">*</span></label>
                    <select class="form-control" id="nacionalidades" name="nationality">
                        <option selected>Seleccione...</option>
                        <?php

                        require_once '../../controllers/CountryController.php';

                        $countryController = new CountryController();

                        $countryList = $countryController->showAll();

                        foreach ($countryList as $item) {
                        ?>
                            <option <?php echo 'value="' . $item->getid() . '"' ?>><?php echo $item->getDemonym(); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <h5>Datos Actor/Actriz</h5>
                <hr>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="nombreArtistico">Nombre Artístico</label>
                    <input type="text" class="form-control" id="nombreArtistico" name="stageName" placeholder="Ingrese el nombre artístico">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="talla">Talla (mts)<span class="required">*</span></label>
                    <input type="number" class="form-control" id="talla" name="height" step="0.01" placeholder="Ingrese la talla">
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="premios">Biografia<span class="required">*</span></label>
                <textarea class="form-control" id="biografia" name="biography" rows="3" placeholder="Máximo 5000 caracteres"></textarea>
            </div>
            <div class="form-group mb-3">
                <label for="premios">Premios</span></label>
                <textarea class="form-control" id="premios" name="awards" rows="3"></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary" name="createBtn">Guardar</button>
                <a href="list.php" class="btn btn-danger">Cancelar</a>
            </div>
        </form>
    </div>
    <script src="../../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/popper.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>

</body>

</html>
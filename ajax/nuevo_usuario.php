<?php
include('is_logged.php'); // Archivo verifica que el usuario que intenta acceder a la URL esté logueado

// Verificando la versión mínima de PHP
if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("Lo siento, este script no puede ejecutarse con una versión de PHP menor a 5.3.7!");
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    require_once("../libraries/password_compatibility_library.php");
}

// Validaciones de los datos del formulario
if (empty($_POST['firstname'])) {
    $errors[] = "Nombres vacíos";
} elseif (empty($_POST['lastname'])) {
    $errors[] = "Apellidos vacíos";
} elseif (empty($_POST['user_name'])) {
    $errors[] = "Nombre de usuario vacío";
} elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
    $errors[] = "Contraseña vacía";
} elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
    $errors[] = "La contraseña y la repetición de la contraseña no son iguales";
} elseif (strlen($_POST['user_password_new']) < 6) {
    $errors[] = "La contraseña debe tener como mínimo 6 caracteres";
} elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
    $errors[] = "El nombre de usuario debe tener entre 2 y 64 caracteres";
} elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
    $errors[] = "El nombre de usuario solo puede contener letras y números, de 2 a 64 caracteres";
} elseif (empty($_POST['user_email'])) {
    $errors[] = "El correo electrónico no puede estar vacío";
} elseif (strlen($_POST['user_email']) > 64) {
    $errors[] = "El correo electrónico no puede tener más de 64 caracteres";
} elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "La dirección de correo electrónico no es válida";
} else {
    // Si todo está correcto, procesar los datos
    require_once("../config/db.php"); // Archivo con configuración de la base de datos
    require_once("../config/conexion.php"); // Archivo con la función que conecta a la base de datos

    // Escapando caracteres especiales
    $firstname = mysqli_real_escape_string($con, strip_tags($_POST["firstname"], ENT_QUOTES));
    $lastname = mysqli_real_escape_string($con, strip_tags($_POST["lastname"], ENT_QUOTES));
    $user_name = mysqli_real_escape_string($con, strip_tags($_POST["user_name"], ENT_QUOTES));
    $user_email = mysqli_real_escape_string($con, strip_tags($_POST["user_email"], ENT_QUOTES));

    // Hasheando la contraseña antes de almacenarla
    $user_password = password_hash($_POST['user_password_new'], PASSWORD_BCRYPT);
    $date_added = date("Y-m-d H:i:s");

    // Verificando si el nombre de usuario o el correo ya existen
    $sql = "SELECT * FROM users WHERE user_name = '" . $user_name . "' OR user_email = '" . $user_email . "';";
    $query_check_user_name = mysqli_query($con, $sql);
    $query_check_user = mysqli_num_rows($query_check_user_name);
    
    if ($query_check_user == 1) {
        $errors[] = "Lo sentimos, el nombre de usuario o la dirección de correo electrónico ya están en uso.";
    } else {
        // Insertando los datos del nuevo usuario
        $sql = "INSERT INTO users (firstname, lastname, user_name, user_password_hash, user_email, date_added)
                VALUES('" . $firstname . "', '" . $lastname . "', '" . $user_name . "', '" . $user_password . "', '" . $user_email . "', '" . $date_added . "');";
        $query_new_user_insert = mysqli_query($con, $sql);

        if ($query_new_user_insert) {
            $messages[] = "La cuenta ha sido creada con éxito.";
        } else {
            $errors[] = "Lo sentimos, el registro falló. Por favor, regrese y vuelva a intentarlo.";
        }
    }
}

// Mostrar errores y mensajes
if (isset($errors)) {
    ?>
    <div class="alert alert-danger" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error!</strong>
        <?php
        foreach ($errors as $error) {
            echo $error;
        }
        ?>
    </div>
    <?php
}

if (isset($messages)) {
    ?>
    <div class="alert alert-success" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>¡Bien hecho!</strong>
        <?php
        foreach ($messages as $message) {
            echo $message;
        }
        ?>
    </div>
    <?php
}
?>
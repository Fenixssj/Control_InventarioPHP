<?php
// Incluye el archivo que verifica si el usuario está logueado
include('is_logged.php'); 

// Verificación de la versión de PHP
if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");
} elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
    require_once("../libraries/password_compatibility_library.php");
}

// Inicialización de variables para almacenar mensajes de error y éxito
$errors = [];
$messages = [];

// Verificación de los datos recibidos por POST
if (empty($_POST['user_id_mod'])) {
    $errors[] = "ID vacío";
} elseif (empty($_POST['user_password_new3']) || empty($_POST['user_password_repeat3'])) {
    $errors[] = "Contraseña vacía";
} elseif ($_POST['user_password_new3'] !== $_POST['user_password_repeat3']) {
    $errors[] = "La contraseña y la repetición de la contraseña no son lo mismo";
} elseif (
    !empty($_POST['user_id_mod']) &&
    !empty($_POST['user_password_new3']) &&
    !empty($_POST['user_password_repeat3']) &&
    ($_POST['user_password_new3'] === $_POST['user_password_repeat3'])
) {
    // Incluir los archivos de configuración de la base de datos
    require_once("../config/db.php");
    require_once("../config/conexion.php");

    // Obtener el ID del usuario y la nueva contraseña
    $user_id = intval($_POST['user_id_mod']);
    $user_password = $_POST['user_password_new3'];

    // Crear el hash de la nueva contraseña
    $user_password_hash = password_hash($user_password, PASSWORD_BCRYPT);

    // Preparar la consulta SQL para actualizar la contraseña
    $stmt = $con->prepare("UPDATE users SET user_password_hash=? WHERE user_id=?");
    $stmt->bind_param("si", $user_password_hash, $user_id);
    
    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si la actualización fue exitosa
    if ($stmt->affected_rows > 0) {
        $messages[] = "La contraseña ha sido modificada con éxito.";
    } else {
        $errors[] = "Lo sentimos, el registro falló. Por favor, regrese y vuelva a intentarlo.";
    }

    // Cerrar la conexión preparada
    $stmt->close();
} else {
    $errors[] = "Un error desconocido ocurrió.";
}

// Mostrar mensajes de error si existen
if (isset($errors) && count($errors) > 0) {
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

// Mostrar mensajes de éxito si existen
if (isset($messages) && count($messages) > 0) {
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

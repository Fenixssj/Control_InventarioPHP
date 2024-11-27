<?php
include('is_logged.php'); // Verifica que el usuario esté logueado

// Configuración de la zona horaria
date_default_timezone_set('America/Santiago'); // Asegura la zona horaria correcta para Chile

/* Inicia validación del lado del servidor */
if (empty($_POST['nombre'])) {
    $errors[] = "Nombre vacío";
} else if (!empty($_POST['nombre'])) {
    /* Conexión a la base de datos */
    require_once("../config/db.php"); // Contiene las variables de configuración para conectar a la base de datos
    require_once("../config/conexion.php"); // Contiene la función que conecta a la base de datos

    // Escape de las variables para prevenir inyecciones SQL y eliminar caracteres no deseados
    $nombre = mysqli_real_escape_string($con, (strip_tags($_POST["nombre"], ENT_QUOTES)));
    $descripcion = mysqli_real_escape_string($con, (strip_tags($_POST["descripcion"], ENT_QUOTES)));
    $date_added = date("Y-m-d H:i:s");

    // Verificar si ya existe una categoría con el mismo nombre
    $sql_check = "SELECT * FROM categorias WHERE nombre_categoria = '$nombre'";
    $query_check = mysqli_query($con, $sql_check);

    if (mysqli_num_rows($query_check) > 0) {
        // Si ya existe una categoría con el mismo nombre
        $errors[] = "La categoría '$nombre' ya existe.";
    } else {
        // Si no existe, insertar la nueva categoría
        $sql = "INSERT INTO categorias (nombre_categoria, descripcion_categoria, date_added) VALUES ('$nombre', '$descripcion', '$date_added')";
        $query_new_insert = mysqli_query($con, $sql);

        if ($query_new_insert) {
            // Obtener la fecha registrada de la categoría recién insertada
            $check_date_query = mysqli_query($con, "SELECT date_added FROM categorias WHERE nombre_categoria = '$nombre'");
            if ($check_date_query && $row = mysqli_fetch_assoc($check_date_query)) {
                $fecha_registrada = $row['date_added'];
                // Mensaje de éxito con la fecha de registro
                $messages[] = "Categoría '$nombre' ha sido ingresada satisfactoriamente. Fecha registrada: $fecha_registrada";
            }
        } else {
            // Error al insertar
            $errors[] = "Lo siento, algo ha salido mal. Intenta nuevamente. " . mysqli_error($con);
        }
    }
} else {
    // Error desconocido
    $errors[] = "Error desconocido.";
}

/* Mostrar mensajes de error */
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

/* Mostrar mensajes de éxito */
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
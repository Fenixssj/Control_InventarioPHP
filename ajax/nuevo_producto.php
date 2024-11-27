<?php
include('is_logged.php'); // Verifica que el usuario está logueado

// Configuración de la zona horaria
date_default_timezone_set('America/Santiago'); // Asegura la zona horaria correcta para Chile

// Validaciones básicas
if (empty($_POST['codigo'])) {
    $errors[] = "Código vacío";
} elseif (empty($_POST['nombre'])) {
    $errors[] = "Nombre del producto vacío";
} elseif ($_POST['stock'] == "") {
    $errors[] = "Stock del producto vacío";
} elseif (empty($_POST['precio'])) {
    $errors[] = "Precio de venta vacío";
} elseif (empty($_POST['categoria'])) {
    $errors[] = "Categoría vacía";
} else {
    // Conexión a la base de datos
    require_once("../config/db.php");
    require_once("../config/conexion.php");
    include("../funciones.php");

    // Escapar y limpiar los datos
    $codigo = mysqli_real_escape_string($con, strip_tags($_POST["codigo"], ENT_QUOTES));
    $nombre = mysqli_real_escape_string($con, strip_tags($_POST["nombre"], ENT_QUOTES));
    $stock = intval($_POST['stock']);
    $id_categoria = intval($_POST['categoria']);
    $precio_venta = floatval($_POST['precio']);
    $date_added = date("Y-m-d H:i:s"); // Fecha actual con la zona horaria configurada

    // Verificar si el código ya existe
    $check_code_query = "SELECT id_producto FROM products WHERE codigo_producto = '$codigo'";
    $check_code_result = mysqli_query($con, $check_code_query);

    if (mysqli_num_rows($check_code_result) > 0) {
        $errors[] = "El código del producto ya está registrado.";
    } else {
        // Insertar producto
        $insert_query = "INSERT INTO products (codigo_producto, nombre_producto, date_added, precio_producto, stock, id_categoria) 
                         VALUES ('$codigo', '$nombre', '$date_added', '$precio_venta', '$stock', '$id_categoria')";
        $insert_result = mysqli_query($con, $insert_query);

        if ($insert_result) {
            $messages[] = "Producto registrado correctamente.";
            $id_producto = mysqli_insert_id($con);

            // Registrar en el historial
            $user_id = $_SESSION['user_id'];
            $firstname = $_SESSION['firstname'];
            $nota = "$firstname agregó $stock producto(s) al inventario";
            guardar_historial($id_producto, $user_id, $date_added, $nota, $codigo, $stock);

            // Confirmar fecha registrada
            $check_date_query = mysqli_query($con, "SELECT date_added FROM products WHERE id_producto = '$id_producto'");
            if ($check_date_query && $row = mysqli_fetch_assoc($check_date_query)) {
                $fecha_registrada = $row['date_added'];
                $messages[] = "Producto registrado con fecha: $fecha_registrada";
            }
        } else {
            $errors[] = "Error en la inserción: " . mysqli_error($con);
        }
    }
}

// Mostrar mensajes
if (isset($errors)) {
    echo "<div class='alert alert-danger'>";
    foreach ($errors as $error) {
        echo "<p><strong>Error:</strong> $error</p>";
    }
    echo "</div>";
}

if (isset($messages)) {
    echo "<div class='alert alert-success'>";
    foreach ($messages as $message) {
        echo "<p><strong>¡Éxito!</strong> $message</p>";
    }
    echo "</div>";
}
?>
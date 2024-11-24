<?php
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
    header("location: login.php");
    exit;
}

/* Conexión a la base de datos */
require_once("config/db.php"); // Contiene las variables de configuración para conectar a la base de datos
require_once("config/conexion.php"); // Contiene la función que conecta a la base de datos

$active_categoria = "active";
$title = "Categorías";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include("head.php"); ?>
</head>
<body>
    <?php include("navbar.php"); ?>

    <div class="container">
        <div class="panel panel-success">
            <div class="panel-heading">
                <div class="btn-group pull-right">
                    <button type='button' class="btn btn-success" data-toggle="modal" data-target="#nuevoCliente">
                        <span class="glyphicon glyphicon-plus"></span> Nueva Categoría
                    </button>
                </div>
                <h4><i class='glyphicon glyphicon-search'></i> Buscar Categorías</h4>
            </div>
            <div class="panel-body">
                <?php
                include("modal/registro_categorias.php");
                include("modal/editar_categorias.php");
                ?>
                <form class="form-horizontal" role="form" id="datos_cotizacion">
                    <div class="form-group row">
                        <label for="q" class="col-md-2 control-label">Nombre</label>
                        <div class="col-md-5">
                            <input type="text" class="form-control" id="q" placeholder="Nombre de la categoría" onkeyup='load(1);'>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-default" onclick='load(1);'>
                                <span class="glyphicon glyphicon-search"></span> Buscar
                            </button>
                            <span id="loader"></span>
                        </div>
                    </div>
                </form>

                <!-- Resultados AJAX -->
                <div id="resultados"></div>
                <div class='outer_div'></div> <!-- Carga los datos AJAX -->
            </div>
        </div>
    </div>
    <hr>
    
    <?php include("footer.php"); ?>

    <!-- Asegúrate de que el archivo js esté correctamente vinculado -->
    <script type="text/javascript" src="js/categorias.js"></script>
</body>
</html>

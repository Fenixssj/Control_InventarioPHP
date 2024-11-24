<?php

/* Conexión a la base de datos */
require_once("../config/db.php"); // Variables de configuración para conectar a la base de datos
require_once("../config/conexion.php"); // Función que conecta a la base de datos
include('is_logged.php'); // Verifica que el usuario esté logueado

$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Asegúrate de que sea un número entero
    $query = mysqli_query($con, "SELECT * FROM users WHERE user_id='$user_id'");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $rw_user = mysqli_fetch_array($query);
        $count = $rw_user['user_id'];
        
        if ($user_id != 1) { // No se puede borrar al administrador
            if ($delete1 = mysqli_query($con, "DELETE FROM users WHERE user_id='$user_id'")) {
                ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Aviso!</strong> Datos eliminados exitosamente.
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Error!</strong> Lo siento, algo ha salido mal. Intenta nuevamente.
                </div>
                <?php
            }
        } else {
            ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Error!</strong> No se puede borrar el usuario administrador.
            </div>
            <?php
        }
    } else {
        ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Error!</strong> Usuario no encontrado.
        </div>
        <?php
    }
}

if ($action == 'ajax') {
    $q = mysqli_real_escape_string($con, (strip_tags($_REQUEST['q'], ENT_QUOTES)));
    $aColumns = array('firstname', 'lastname'); // Columnas de búsqueda
    $sTable = "users";
    $sWhere = "";

    if ($_GET['q'] != "") {
        $sWhere = "WHERE (";
        for ($i = 0; $i < count($aColumns); $i++) {
            $sWhere .= $aColumns[$i] . " LIKE '%" . $q . "%' OR ";
        }
        $sWhere = substr_replace($sWhere, "", -3);
        $sWhere .= ')';
    }
    $sWhere .= " ORDER BY user_id DESC";

    include 'pagination.php'; // Archivo de paginación
    $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
    $per_page = 10; // Registros por página
    $adjacents = 4; // Páginas adyacentes
    $offset = ($page - 1) * $per_page;

    // Contar el total de filas
    $count_query = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
    $row = mysqli_fetch_array($count_query);
    $numrows = $row['numrows'];
    $total_pages = ceil($numrows / $per_page);
    $reload = './usuarios.php';

    // Consulta principal para obtener los datos
    $sql = "SELECT * FROM $sTable $sWhere LIMIT $offset, $per_page";
    $query = mysqli_query($con, $sql);

    if ($numrows > 0) {
        ?>
        <div class="table-responsive">
            <table class="table">
                <tr class="success">
                    <th>ID</th>
                    <th>Nombres</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Agregado</th>
                    <th><span class="pull-right">Acciones</span></th>
                </tr>
                <?php
                while ($row = mysqli_fetch_array($query)) {
                    $user_id = $row['user_id'];
                    $fullname = $row['firstname'] . " " . $row["lastname"];
                    $user_name = $row['user_name'];
                    $user_email = $row['user_email'];
                    $date_added = date('d/m/Y', strtotime($row['date_added']));
                    ?>
                    <input type="hidden" value="<?php echo $row['firstname']; ?>" id="nombres<?php echo $user_id; ?>">
                    <input type="hidden" value="<?php echo $row['lastname']; ?>" id="apellidos<?php echo $user_id; ?>">
                    <input type="hidden" value="<?php echo $user_name; ?>" id="usuario<?php echo $user_id; ?>">
                    <input type="hidden" value="<?php echo $user_email; ?>" id="email<?php echo $user_id; ?>">
                    <tr>
                        <td><?php echo $user_id; ?></td>
                        <td><?php echo $fullname; ?></td>
                        <td><?php echo $user_name; ?></td>
                        <td><?php echo $user_email; ?></td>
                        <td><?php echo $date_added; ?></td>
                        <td>
                            <span class="pull-right">
                                <a href="#" class='btn btn-default' title='Editar usuario' 
                                   onclick="obtener_datos('<?php echo $user_id; ?>');" data-toggle="modal" data-target="#myModal2">
                                    <i class="glyphicon glyphicon-edit"></i>
                                </a>
                                <a href="#" class='btn btn-default' title='Cambiar contraseña' 
                                   onclick="get_user_id('<?php echo $user_id; ?>');" data-toggle="modal" data-target="#myModal3">
                                    <i class="glyphicon glyphicon-cog"></i>
                                </a>
                                <a href="#" class='btn btn-default' title='Borrar usuario' 
                                   onclick="eliminar('<?php echo $user_id; ?>')">
                                    <i class="glyphicon glyphicon-trash"></i>
                                </a>
                            </span>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td colspan=9>
                        <span class="pull-right">
                            <?php echo paginate($reload, $page, $total_pages, $adjacents); ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
}
?>

<?php
session_start();
if (!isset($_SESSION['user_login_status']) AND $_SESSION['user_login_status'] != 1) {
    header("location: login.php");
    exit;
}

/* Connect To Database*/
require_once ("config/db.php");//Contiene las variables de configuracion para conectar a la base de datos
require_once ("config/conexion.php");//Contiene funcion que conecta a la base de datos
include("funciones.php");

// Establece la zona horaria para Chile
date_default_timezone_set('America/Santiago'); 

$active_productos="active";
$active_clientes="";
$active_usuarios="";    
$title="Producto";

if (isset($_POST['reference']) and isset($_POST['quantity'])) {
    $quantity = intval($_POST['quantity']);
    $reference = mysqli_real_escape_string($con, (strip_tags($_POST["reference"], ENT_QUOTES)));
    $id_producto = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $firstname = $_SESSION['firstname'];
    $nota = "$firstname agregó $quantity producto(s) al inventario";
    $fecha = date("Y-m-d H:i:s");  // Fecha y hora actual en formato 'Y-m-d H:i:s'
    guardar_historial($id_producto, $user_id, $fecha, $nota, $reference, $quantity);
    $update = agregar_stock($id_producto, $quantity);
    if ($update == 1) {
        $message = 1;
    } else {
        $error = 1;
    }
}

if (isset($_POST['reference_remove']) and isset($_POST['quantity_remove'])) {
    $quantity = intval($_POST['quantity_remove']);  // Asegúrate de que la cantidad sea un número entero
    $reference = mysqli_real_escape_string($con, (strip_tags($_POST["reference_remove"], ENT_QUOTES)));
    $id_producto = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $firstname = $_SESSION['firstname'];
    $nota = "$firstname eliminó $quantity producto(s) del inventario";
    $fecha = date("Y-m-d H:i:s");

    // Obtener el stock actual del producto
    $query = mysqli_query($con, "SELECT stock FROM products WHERE id_producto = '$id_producto'");
    $row = mysqli_fetch_array($query);
    $current_stock = $row['stock']; // Stock actual del producto

    // Validar si la cantidad a eliminar es mayor que el stock disponible
    if ($quantity > $current_stock) {
        // Si la cantidad a eliminar es mayor que el stock, mostrar mensaje de error
        $error = "No puedes eliminar más productos de los que hay en el inventario. Stock disponible: " . $current_stock;
    } else {
        // Si la cantidad es válida, proceder con la eliminación
        $quantity = floor($quantity);  // Redondea la cantidad hacia abajo

        // Guardar historial
        guardar_historial($id_producto, $user_id, $fecha, $nota, $reference, $quantity); // Guardar en historial

        // Actualizar el stock al eliminar productos
        $update = eliminar_stock($id_producto, $quantity);

        if ($update == 1) {
            $message = 1;
        } else {
            $error = 1;
        }
    }
}

if (isset($_GET['id'])) {
    $id_producto = intval($_GET['id']);
    $query = mysqli_query($con, "SELECT * FROM products WHERE id_producto = '$id_producto'");
    $row = mysqli_fetch_array($query);
} else {
    die("Producto no existe");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("head.php");?>
</head>
<body>
    <?php
    include("navbar.php");
    include("modal/agregar_stock.php");
    include("modal/eliminar_stock.php");
    include("modal/editar_productos.php");
    ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-4 col-sm-offset-2 text-center">
                                <img class="item-img img-responsive" src="img/stock.png" alt=""> 
                                <br>
                                <a href="#" class="btn btn-danger" onclick="eliminar('<?php echo $row['id_producto']; ?>')" title="Eliminar"> <i class="glyphicon glyphicon-trash"></i> Eliminar </a> 
                                <a href="#myModal2" data-toggle="modal" data-codigo='<?php echo $row['codigo_producto']; ?>' data-nombre='<?php echo $row['nombre_producto']; ?>' data-categoria='<?php echo $row['id_categoria']; ?>' data-precio='<?php echo $row['precio_producto']; ?>' data-stock='<?php echo $row['stock']; ?>' data-id='<?php echo $row['id_producto']; ?>' class="btn btn-info" title="Editar"> <i class="glyphicon glyphicon-pencil"></i> Editar </a>    
                            </div>

                            <div class="col-sm-4 text-left">
                                <div class="row margin-btm-20">
                                    <div class="col-sm-12">
                                        <span class="item-title"> <?php echo $row['nombre_producto']; ?></span>
                                    </div>
                                    <div class="col-sm-12 margin-btm-10">
                                        <span class="item-number"><?php echo $row['codigo_producto']; ?></span>
                                    </div>
                                    <div class="col-sm-12 margin-btm-10">
                                    </div>
                                    <div class="col-sm-12">
                                        <span class="current-stock">Stock disponible</span>
                                    </div>
                                    <div class="col-sm-12 margin-btm-10">
                                        <span class="item-quantity"><?php echo number_format($row['stock'], 2); ?></span>
                                    </div>
                                    <div class="col-sm-12">
                                        <span class="current-stock"> Precio venta  </span>
                                    </div>
                                    <div class="col-sm-12">
                                        <span class="item-price">$ <?php echo number_format($row['precio_producto'], 2); ?></span>
                                    </div>
                                    <div class="col-sm-12 margin-btm-10">
                                    </div>
                                    <div class="col-sm-6 col-xs-6 col-md-4 ">
                                        <a href="" data-toggle="modal" data-target="#add-stock"><img width="100px"  src="img/stock-in.png"></a>
                                    </div>
                                    <div class="col-sm-6 col-xs-6 col-md-4">
                                        <a href="" data-toggle="modal" data-target="#remove-stock"><img width="100px"  src="img/stock-out.png"></a>
                                    </div>
                                    <div class="col-sm-12 margin-btm-10">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-sm-8 col-sm-offset-2 text-left">
                                <div class="row">
                                    <?php
                                    if (isset($message)) {
                                        ?>
                                        <div class="alert alert-success alert-dismissible" role="alert">
                                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                          <strong>Aviso!</strong> Datos procesados exitosamente.
                                        </div>    
                                    <?php
                                    }
                                    if (isset($error)) {
                                        ?>
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                          <strong>Error!</strong> <?php echo $error; ?>
                                        </div>    
                                    <?php
                                    }
                                    ?>    
                                     <table class='table table-bordered'>
                                        <tr>
                                            <th class='text-center' colspan=5 >HISTORIAL DE INVENTARIO</th>
                                        </tr>
                                        <tr>
                                            <td>Fecha</td>
                                            <td>Hora</td>
                                            <td>Descripción</td>
                                            <td>Referencia</td>
                                            <td class='text-center'>Total</td>
                                        </tr>
                                        <?php
                                            $query = mysqli_query($con, "SELECT * FROM historial WHERE id_producto = '$id_producto'");
                                            while ($row = mysqli_fetch_array($query)) {
                                                ?>
                                        <tr>
                                        	<td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td> <!-- Fecha -->
											<td><?php echo date('H:i:s', strtotime($row['fecha'])); ?></td> <!-- Hora -->
                                            <td><?php echo $row['nota']; ?></td>
                                            <td><?php echo $row['referencia']; ?></td>
                                            <td class='text-center'><?php echo number_format($row['cantidad'], 2); ?></td>
                                        </tr>        
                                        <?php
                                        	}
                                        ?>
                                     </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("footer.php"); ?>
</body>
</html>

<script>
// Modal and other functions
$( "#editar_producto" ).submit(function( event ) {
  $('#actualizar_datos').attr("disabled", true);
  var parametros = $(this).serialize();
  $.ajax({
      type: "POST",
      url: "editar_producto.php",
      data: parametros,
      beforeSend: function(objeto){
        $("#resultados_ajax").html("Enviando...");
      },
      success: function(datos){
        $("#resultados_ajax").html(datos);
        $('#actualizar_datos').attr("disabled", false);
      }
  });
  event.preventDefault();
})

$('#myModal2').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var codigo = button.data('codigo');
  var nombre = button.data('nombre');
  var categoria = button.data('categoria');
  var precio = button.data('precio');
  var stock = button.data('stock');
  var id = button.data('id');
  var modal = $(this);
  modal.find('.modal-body #mod_codigo').val(codigo);
  modal.find('.modal-body #mod_nombre').val(nombre);
  modal.find('.modal-body #mod_categoria').val(categoria);
  modal.find('.modal-body #mod_precio').val(precio);
  modal.find('.modal-body #mod_stock').val(stock);
  modal.find('.modal-body #mod_id').val(id);
})

function eliminar (id) {
  var q = $("#q").val();
  if (confirm("Realmente deseas eliminar el producto")) {
    location.replace('stock.php?delete=' + id);
  }
}
</script>

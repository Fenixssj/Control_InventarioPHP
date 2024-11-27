<?php
// Verifica la versión mínima de PHP requerida
if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("Lo siento, este sistema requiere PHP 5.3.7 o superior.");
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    require_once("libraries/password_compatibility_library.php"); // Compatibilidad para versiones anteriores a PHP 5.5
}

// Incluir configuración de conexión a la base de datos
require_once("config/db.php");

// Cargar la clase Login
require_once("classes/Login.php");

// Crear el objeto de Login
$login = new Login();

// Si el usuario está autenticado
if ($login->isUserLoggedIn() == true) {
    session_start();
    $_SESSION['username'] = $login->getUserName(); // Asegúrate de que esta función obtenga el nombre de usuario
    header("location: stock.php");
    exit();
} else {
    // Si el usuario no está autenticado
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Simple Stock | Login</title>
      <!-- Bootstrap CSS -->
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
      <link href="css/login.css" type="text/css" rel="stylesheet" media="screen,projection"/>
    </head>
    <body>
      <div class="container">
          <div class="card card-container">
              <img id="profile-img" class="profile-img-card" src="img/avatar_2x.png" />
              <p id="profile-name" class="profile-name-card"></p>
              <form method="post" accept-charset="utf-8" action="login.php" name="loginform" autocomplete="off" role="form" class="form-signin">
              <?php
                  // Mostrar errores o mensajes del login
                  if (isset($login)) {
                      if ($login->errors) {
                          echo '<div class="alert alert-danger alert-dismissible" role="alert">';
                          echo '<strong>Error!</strong>';
                          foreach ($login->errors as $error) {
                              echo $error;
                          }
                          echo '</div>';
                      }
                      if ($login->messages) {
                          echo '<div class="alert alert-success alert-dismissible" role="alert">';
                          echo '<strong>Aviso!</strong>';
                          foreach ($login->messages as $message) {
                              echo $message;
                          }
                          echo '</div>';
                      }
                  }
              ?>
                  <span id="reauth-email" class="reauth-email"></span>
                  <input class="form-control" placeholder="Usuario" name="user_name" type="text" required autofocus>
                  <input class="form-control" placeholder="Contraseña" name="user_password" type="password" required>
                  <button type="submit" class="btn btn-lg btn-success btn-block btn-signin" name="login">Iniciar Sesión</button>
              </form>
          </div>
      </div>
    </body>
    </html>
    <?php
}
?>

<?php

/**
 * Class login
 * Handles the user's login and logout process
 */
class Login
{
    /**
     * @var object The database connection
     */
    private $db_connection = null;

    /**
     * @var array Collection of error messages
     */
    public $errors = array();

    /**
     * @var array Collection of success / neutral messages
     */
    public $messages = array();

    /**
     * Constructor, automatically starts whenever an object of this class is created
     */
    public function __construct()
    {
        // Create or resume a session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Handle user logout
        if (isset($_GET["logout"])) {
            $this->doLogout();
        }
        // Handle user login
        elseif (isset($_POST["login"])) {
            $this->dologinWithPostData();
        }
    }

    /**
     * Log in with post data
     */
    private function dologinWithPostData()
    {
        // Validate login form inputs
        if (empty($_POST['user_name'])) {
            $this->errors[] = "El campo de usuario está vacío.";
        } elseif (empty($_POST['user_password'])) {
            $this->errors[] = "El campo de contraseña está vacío.";
        } elseif (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {

            // Create a database connection
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // Check connection and set character set
            if (!$this->db_connection->set_charset("utf8")) {
                $this->errors[] = $this->db_connection->error;
            }

            if (!$this->db_connection->connect_errno) {
                // Escape input to prevent SQL injection
                $user_name = $this->db_connection->real_escape_string($_POST['user_name']);

                // Query user information
                $sql = "SELECT user_id, user_name, firstname, user_email, user_password_hash
                        FROM users
                        WHERE user_name = '" . $user_name . "' OR user_email = '" . $user_name . "';";
                $result_of_login_check = $this->db_connection->query($sql);

                // Check if user exists
                if ($result_of_login_check->num_rows == 1) {
                    $result_row = $result_of_login_check->fetch_object();

                    // Verify password using password_verify()
                    if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
                        // Store user data in the session
                        $_SESSION['user_id'] = $result_row->user_id;
                        $_SESSION['firstname'] = $result_row->firstname;
                        $_SESSION['user_name'] = $result_row->user_name;
                        $_SESSION['user_email'] = $result_row->user_email;
                        $_SESSION['user_login_status'] = 1;
                    } else {
                        $this->errors[] = "Usuario y/o contraseña no coinciden.";
                    }
                } else {
                    $this->errors[] = "Usuario y/o contraseña no coinciden.";
                }
            } else {
                $this->errors[] = "Problema de conexión a la base de datos.";
            }
        }
    }

    /**
     * Perform the logout
     */
    public function doLogout()
    {
        // Clear the session and destroy it
        $_SESSION = array();
        session_destroy();
        $this->messages[] = "Has sido desconectado.";
    }

    /**
     * Return the current state of the user's login
     * @return boolean User's login status
     */
    public function isUserLoggedIn()
    {
        // Check if the user login status is set and true
        if (isset($_SESSION['user_login_status']) && $_SESSION['user_login_status'] == 1) {
            return true;
        }
        return false;
    }

    /**
     * Get the logged-in user's name
     * @return string|null The user's name or null if not logged in
     */
    public function getUserName()
    {
        return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
    }

    /**
     * Get the logged-in user's first name
     * @return string|null The user's first name or null if not logged in
     */
    public function getFirstName()
    {
        return isset($_SESSION['firstname']) ? $_SESSION['firstname'] : null;
    }
}

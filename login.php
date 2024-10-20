<?php
session_start();
$_SESSION['enviar'] = false;

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Lógica de inserción y redirección (Registro)
if (isset($_POST['enviar'])) {
    $user_name = mysqli_real_escape_string($connect, $_POST['name']);
    $user_last_name = mysqli_real_escape_string($connect, $_POST['last_name']);
    $user_phone = mysqli_real_escape_string($connect, $_POST['phone']);
    $user_email = mysqli_real_escape_string($connect, $_POST['email']);
    $user_password = mysqli_real_escape_string($connect, $_POST['password']);

    // Cifrar la contraseña antes de almacenarla
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

    // Query para insertar usuarios
    $insert_query = "INSERT INTO users (name, last_name, email, password, phone) VALUES ('$user_name', '$user_last_name', '$user_email', '$hashed_password', '$user_phone')";

    if (mysqli_query($connect, $insert_query)) {
        // Redireccionar después de la inserción exitosa
        header('Location: reservations.php?id='.$user_id);
        exit();
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error al registrarse: " . mysqli_error($connect) . "</div>";
    }
}

// Verificación del login (Inicio de sesión)
if (isset($_POST['comprobar'])) {
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);

    if($username == 'admin' && $password == 'admin') {
        header('Location: admin.php');
        exit();
    } else {
        // Query para validar las credenciales del usuario
        $select_query = "SELECT * FROM users WHERE email='$username'";
        $result = mysqli_query($connect, $select_query);
    
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $password_from_db = $user['password']; // Aquí estará el hash de la contraseña
            $user_id = $user['id'];
    
            // Verificar la contraseña usando password_verify
            if (password_verify($password, $password_from_db)) {
                // Redireccionar si las credenciales son correctas
                header('Location: reservations.php?id='.$user_id);
                exit();
            } else {
                echo "<div class='alert alert-info' role='alert'>No se encontraron citas.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Rol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            padding: 50px 0;
        }

        h1 {
            text-align: center;
        }

        .form--container {
            width: 40%;
            padding: 20px;
            margin: 0 auto;
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h1 class="mb-5">Reservaciones de Hotel</h1>
        <div class="form--container">
            <!-- Formulario de inicio de sesión -->
            <form method="post" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Correo Electrónico</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="comprobar" class="btn btn-primary mb-3">Iniciar Sesión</button>
            </form>
            <button type="button" class="btn btn-secondary" onclick="showModal()">Registrarse</button>
        </div>
    </div>

    <!-- Formulario oculto para seleccionar rol -->
    <form id="roleForm" action="index.php" method="post" style="display: none;">
        <input type="hidden" name="role" id="roleInput">
    </form>

    <!-- Modal para registro -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Registrarse - Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Apellido:</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Teléfono:</label>
                            <input type="text" class="form-control" id="phone_number" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password1" class="form-label">Contraseña:</label>
                            <input type="password" class="form-control" id="password1" name="password" required>
                        </div>
                        <button type="submit" name="enviar" id="submit" class="btn btn-primary">Registrarse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showModal() {
            var modal = new bootstrap.Modal(document.getElementById('loginModal'));
            modal.show();
        }
    </script>
</body>
</html>

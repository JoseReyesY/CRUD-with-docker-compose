<?php
session_start();
$_SESSION['enviar']=false;
$user_id = $_GET['id'];

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Verifica si el usuario ha iniciado sesión
if ($user_id != null) {
    // Consulta para obtener los datos del usuario
    $query = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_name = $user['name']; // Cambia 'name' por el campo correspondiente en tu tabla
        $user_last_name = $user['last_name'];
    } else {
        $user_name = "Invitado"; // Valor por defecto si no se encuentra el usuario
    }
} else {
    $user_name = "Invitado"; // Valor por defecto si no hay sesión activa
}

// Lógica de inserción y redirección (Registro)
if (isset($_POST['submit'])) {
    $start_date = mysqli_real_escape_string($connect, $_POST['reservation_arrive_date']);
    $finish_date = mysqli_real_escape_string($connect, $_POST['reservation_departure_date']);
    $room_type = mysqli_real_escape_string($connect, $_POST['type']);

    // Crear objetos DateTime para calcular la diferencia
    $date_inicio = new DateTime($start_date);
    $date_fin = new DateTime($finish_date);

    // Calcular la diferencia
    $diferencia = $date_inicio->diff($date_fin);
    $dias = $diferencia->days;

    // Query para seleccionar habitaciones
    $select_query = "SELECT * FROM rooms WHERE available='1' and type='$room_type'";
    $result = mysqli_query($connect, $select_query);

    if (mysqli_num_rows($result) > 0) {
        $room = mysqli_fetch_assoc($result);
        $id_room = $room['room_number'];
        $type = $room['type'];

        if($type == 1) {
            $price = 200 * $dias;
        } else if($type == 2) {
            $price = 350 * $dias;
        }

        // Query para insertar usuarios
        $insert_query = "INSERT INTO reservations (id_user, id_room, start_date, finish_date, price) VALUES ('$user_id', '$id_room', '$start_date', '$finish_date', '$price')";

        if (mysqli_query($connect, $insert_query)) {
            $update_query = "UPDATE rooms SET available='0' WHERE room_number='$id_room'";

            $reservation_id = mysqli_insert_id($connect);
            if (mysqli_query($connect, $update_query)) {
                // Realizar un SELECT para obtener detalles de la reservación
                $confirmation_query = "SELECT * FROM reservations WHERE id='$reservation_id'";
                $confirmation_result = mysqli_query($connect, $confirmation_query);
                $reservation_details = mysqli_fetch_assoc($confirmation_result);

                // Redireccionar a la página de confirmación
                header('Location: confirmation.php?reservation_id=' . $reservation_id);
                exit();
            }
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error al crear la reservacion: " . mysqli_error($connect) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger' role='alert'>No hay disponibilidad en las fechas seleccionadas</div>";
    }
}

// Lógica de inserción y redirección (Registro)
if (isset($_POST['search'])) {
    $reservation_id = mysqli_real_escape_string($connect, $_POST['reservation_id']);
    header('Location: confirmation.php?reservation_id=' . $reservation_id);
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        header {
            padding: 20px;
            text-align: center;
            background-color: #007bff;
            color: white;
        }

        .centered {
            width: 100%;
            height: 52px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: right;
        }

        .form--container {
            width: 40%;
            padding: 20px;
            margin: 0 auto;
        }

        .info--container {
            width: 100%;
            margin-bottom: 16px;
            display: flex;
            gap: 70px;
        }

        .info-sub-container {
            width: 50%;
        }

        #patient_name {
            width: 100%;
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
    <body>
        <header>
            <h1>Reservaciones Hotel</h1>
        </header>
        
        <div class="centered">
            <button type="submit" name="submit" class="btn btn-primary" onclick="showModal()">Buscar Reservación</button>

        </div>

        <div class="container mt-5">
            <h2>Bienvenido, <?php echo $user_name; ?></h2>
            <h3>Hacer una nueva reservación</h3>
            <form action="" method="post" class="mb-4">
                <!-- Tipo de habitacion a seleccionar -->
                <div class="mb-3">
                    <label for="type" class="form-label">Tipo de Habitación (sencilla o doble):</label>
                    <select name="type" id="type" required class="form-control">
                        <option value="0">Seleccione la habitación</option>
                        <option value="1">Sencilla</option>
                        <option value="2">Doble</option>
                    </select>
                </div>

                <!-- Fechas deseadas por el usuario -->
                <div class="mb-3">
                    <label for="reservation_arrive_date" class="form-label">Fecha de Llegada:</label>
                    <input type="date" id="reservation_arrive_date" name="reservation_arrive_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="reservation_departure_date" class="form-label">Fecha de Salida:</label>
                    <input type="date" id="reservation_departure_date" name="reservation_departure_date" class="form-control" required>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Agregar Cita</button>
            </form>
        </div>

        <!-- Modal para editar cita -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Buscar Reservación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit-id">
                            <div class="mb-3">
                                <label for="reservation_id" class="form-label">Identificador de la Reservación:</label>
                                <input type="number" id="reservation_id" name="reservation_id" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="search" class="btn btn-primary">Buscar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            function showModal() {
                var modal = new bootstrap.Modal(document.getElementById('searchModal'));
                modal.show();
            }
        </script>
    </body>
</html>

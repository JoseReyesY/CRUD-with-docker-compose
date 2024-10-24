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

    // Calcular la diferencia de días
    $diferencia = $date_inicio->diff($date_fin);
    $dias = $diferencia->days;

    // Generar un identificador alfanumérico aleatorio de 12 caracteres
    $reservation_id = bin2hex(random_bytes(4)); // Genera un string aleatorio de 12 caracteres

    // Query para seleccionar habitaciones disponibles en base a las fechas y tipo de habitación
    $select_query = "
        SELECT * 
        FROM rooms 
        WHERE available='1' 
        AND type='$room_type'
        AND room_number NOT IN (
            SELECT id_room 
            FROM reservations 
            WHERE ('$start_date' < finish_date AND '$finish_date' > start_date)
        )
    ";
    $result = mysqli_query($connect, $select_query);

    if (mysqli_num_rows($result) > 0) {
        $room = mysqli_fetch_assoc($result);
        $id_room = $room['room_number'];
        $type = $room['type'];

        // Calcular el precio basado en el tipo de habitación y los días de la estancia
        if($type == 1) {
            $price = 200 * $dias;
        } else if($type == 2) {
            $price = 350 * $dias;
        }

        // Query para insertar la reservación con el ID único generado
        $insert_query = "INSERT INTO reservations (id, id_user, id_room, start_date, finish_date, price) 
                         VALUES ('$reservation_id', '$user_id', '$id_room', '$start_date', '$finish_date', '$price')";

        if (mysqli_query($connect, $insert_query)) {
            $update_query = "UPDATE rooms SET available='0' WHERE room_number='$id_room'";

            if (mysqli_query($connect, $update_query)) {
                // Redireccionar a la página de confirmación con el ID de reservación
                header('Location: confirmation.php?reservation_id=' . $reservation_id . '&last_name=' . $user_last_name);
                exit();
            }
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error al crear la reservación: " . mysqli_error($connect) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger mb-0' role='alert'>No hay habitaciones disponibles para las fechas seleccionadas.</div>";
    }
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

        <div class="container mt-5">
            <h2>Bienvenido, <?php echo $user_name; ?> <?php echo $user_last_name; ?></h2>
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

                <button type="submit" name="submit" class="btn btn-primary">Agregar Reservación</button>
            </form>
        </div>
    </body>
</html>

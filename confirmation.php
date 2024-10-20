<?php
session_start();
$reservation_id = $_GET['reservation_id'];

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Verificar si se ha recibido el ID de la reservación
if (isset($_GET['reservation_id'])) {
    // Consulta para obtener detalles de la reservación
    $query = "SELECT r.*, u.name as user_name, u.last_name as user_last_name, ro.type as room_type
              FROM reservations r
              JOIN users u ON r.id_user = u.id
              JOIN rooms ro ON r.id_room = ro.room_number
              WHERE r.id = '$reservation_id'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $reservation = mysqli_fetch_assoc($result);
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error: No se encontró la reservación.</div>";
        exit();
    }
} else {
    echo "<div class='alert alert-danger' role='alert'>Error: ID de reservación no proporcionado.</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reservación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            padding: 50px 0;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Confirmación de Reservación</h1>
        
        <div class="alert alert-success" role="alert">
            <strong>¡Reserva Confirmada!</strong>
        </div>
        
        <h3>Detalles de la Reservación</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ID de Reservación</td>
                    <td><?php echo $reservation['id']; ?></td>
                </tr>
                <tr>
                    <td>Nombre del Usuario</td>
                    <td><?php echo $reservation['user_name'] . ' ' . $reservation['user_last_name']; ?></td>
                </tr>
                <tr>
                    <td>Tipo de Habitación</td>
                    <td><?php echo $reservation['room_type']; ?></td>
                </tr>
                <tr>
                    <td>Fecha de Llegada</td>
                    <td><?php echo $reservation['start_date']; ?></td>
                </tr>
                <tr>
                    <td>Fecha de Salida</td>
                    <td><?php echo $reservation['finish_date']; ?></td>
                </tr>
                <tr>
                    <td>Precio</td>
                    <td>$<?php echo $reservation['price']; ?></td>
                </tr>
            </tbody>
        </table>

        <a href="index.php" class="btn btn-primary">Volver a la Página Principal</a>
    </div>

    

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

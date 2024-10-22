<?php
session_start();

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Edición de reservaciones
if (isset($_POST['editReservation'])) {
    $id = intval($_POST['room_number']);
    $type = mysqli_real_escape_string($connect, $_POST['type']);
    $available = mysqli_real_escape_string($connect, $_POST['available']);

    // Query para actualizar reservaciones
    $update_query = "UPDATE rooms SET type = '$type', available = '$available' WHERE room_number = $id";
    mysqli_query($connect, $update_query);
}

// Eliminación de habitaciones
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $delete_query = "DELETE FROM reservation WHERE room_number = $id";
    mysqli_query($connect, $delete_query);
}

// Número de habitaciones por página
$rooms_per_page = 10;

// Página actual (si no se envía, asume que es la primera página)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calcular el desplazamiento (OFFSET) para la consulta
$offset = ($page - 1) * $rooms_per_page;

// Consulta para contar el número total de habitaciones
$total_query = "SELECT COUNT(*) as total FROM rooms";
$total_result = mysqli_query($connect, $total_query);
$total_rooms = mysqli_fetch_assoc($total_result)['total'];

// Calcular el número total de páginas
$total_pages = ceil($total_rooms / $rooms_per_page);

// Consulta para obtener las habitaciones para la página actual
$query = "SELECT r.*, u.name as user_name, u.last_name as user_last_name, ro.type as room_type
            FROM reservations r
            JOIN users u ON r.id_user = u.id
            JOIN rooms ro ON r.id_room = ro.room_number
            LIMIT $rooms_per_page OFFSET $offset";
$response = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
        }

        .container {
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        header {
            padding: 20px;
            text-align: center;
            background-color: #007bff;
            color: white;
        }

        h1 {
            color: #fff;
        }

        h2 {
            color: #007bff;
        }

        .title--container {
            display: flex;
            justify-content: space-between;
        }

        .centered {
            width: 100%;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
    <header>
        <h1>Reservaciones Hotel</h1>
    </header>

    <div class="container mt-5">
        <div class="title--container mb-3">
            <h2>Lista de Reservaciones Registradas</h2>
        </div>

        <?php
        if (mysqli_num_rows($response) > 0) {
            echo '<table class="table table-bordered">';
            echo '<thead><tr><th>Identificador de la reservación</th><th>Nombre del Huesped</th><th>Fecha de Llegada</th><th>Fecha de Salida</th><th>Acciones</th></tr></thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($response)) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>'; 
                echo '<td>' . $row['u.name'] . ' ' . $row['u.last_name'] . '</td>';
                echo '<td>' . ($row['available'] == 1 ? 'Disponible' : 'Ocupada') . '</td>';
                echo '<td>';
                echo '<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" data-id="' . $row['room_number'] . '" data-name="'. $row['room_number'] . '" data-name="' . $row['type'] . '" data-details="' . $row['available'] . '">Editar</button>';
                echo ' <a href="?delete=' . $row['room_number'] . '" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta habitación?\')">Eliminar</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo "<div class='alert alert-info' role='alert'>No se encontraron reservaciones.</div>";
        }

        // Mostrar paginación
        echo '<nav aria-label="Page navigation">';
        echo '<ul class="pagination">';

        // Botón "Anterior"
        if ($page > 1) {
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Anterior</a></li>';
        }

        // Botones numéricos de las páginas
        for ($i = 1; $i <= $total_pages; $i++) {
            echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">';
            echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
            echo '</li>';
        }

        // Botón "Siguiente"
        if ($page < $total_pages) {
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '">Siguiente</a></li>';
        }

        echo '</ul>';
        echo '</nav>';
        ?>
    </div>

    <!-- Modal para crear habitación -->
    <div class="modal fade" id="roomModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Agregar habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="type" class="form-label">Seleccione el tipo de habitación:</label>
                            <select name="type" id="type" required class="form-control">
                                <option value="1">Sencilla</option>
                                <option value="2">Doble</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="available" class="form-label">Seleccione la disponibilidad de la habitación:</label>
                            <select name="available" id="available" required class="form-control">
                                <option value="1">Disponible</option>
                                <option value="0">Ocupada</option>
                            </select>
                        </div>
                        <button type="submit" name="createRoom" id="createRoom" class="btn btn-primary">Registrarse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar habitación -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Habitación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="room_number" id="edit-id">
                        <div class="mb-3">
                            <label for="edit-room_type" class="form-label">Seleccione el tipo de habitación:</label>
                            <select name="type" id="edit-room_type" required class="form-control">
                                <option value="1">Sencilla</option>
                                <option value="2">Doble</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-available" class="form-label">Seleccione la disponibilidad de la habitación:</label>
                            <select name="available" id="edit-available" required class="form-control">
                                <option value="1">Disponible</option>
                                <option value="0">Ocupada</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="editRoom" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="centered mt-3">
        <a class="btn btn-primary" href="admin.php">Volver a la página principal</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var roomModal = new bootstrap.Modal(document.getElementById('roomModal'));

        function showModal() {
            roomModal.show();
        }

        var editModal = new bootstrap.Modal(document.getElementById('editModal'));

        document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var type = button.getAttribute('data-name');
            var available = button.getAttribute('data-details');

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-room_type').value = type;
            document.getElementById('edit-available').value = available;
        });
    </script>
</body>
</html>

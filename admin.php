<?php
session_start();

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Registro de habitaciones
if (isset($_POST['createRoom'])) {
    $room_type = mysqli_real_escape_string($connect, $_POST['type']);
    $room_is_available = mysqli_real_escape_string($connect, $_POST['available']);

    // Query para insertar usuarios
    $insert_query = "INSERT INTO rooms (type, available) VALUES ('$room_type', '$room_is_available')";

    if (mysqli_query($connect, $insert_query)) {
        // Redireccionar después de la inserción exitosa
        header('Location:'.$_SERVER['PHP_SELF'],);
        exit();
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error al registrarse: " . mysqli_error($connect) . "</div>";
    }
}

// Edición de habitaciones
if (isset($_POST['editRoom'])) {
    $id = intval($_POST['room_number']);
    $type = mysqli_real_escape_string($connect, $_POST['type']);
    $available = mysqli_real_escape_string($connect, $_POST['available']);

    $update_query = "UPDATE rooms SET type = '$type', available = '$available' WHERE room_number = $id";
    //$update_query = "SELECT rooms SET patient_name = '$patient_name', appointment_date = '$appointment_date', details = '$details' WHERE id = $id";

    mysqli_query($connect, $update_query);
}

// Eliminación de habitaciones
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $delete_query = "DELETE FROM rooms WHERE room_number = $id";
    mysqli_query($connect, $delete_query);
}


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
</head>
    <body>
        <header>
            <h1>Reservaciones Hotel</h1>
        </header>

        <div class="container mt-5">
            <div class="title--container mb-3">
                <h2>Lista de Habitaciones Registradas</h2>
                <button class="btn btn-primary" onclick="showModal()">Agregar habitación</button>
            </div>
            <?php
            $query = "SELECT * FROM rooms";
            $response = mysqli_query($connect, $query);

            if ($response) {
                echo '<table class="table table-bordered">';
                echo '<thead><tr><th>Número de Habitación</th><th>Tipo</th><th>Disponible</th>';
                echo '<tbody>';
                while ($row = mysqli_fetch_assoc($response)) {
                    echo '<tr>';
                    echo '<td>' . $row['room_number'] . '</td>'; 
                    echo '<td>' . $row['type'] . '</td>';
                    echo '<td>' . $row['available'] . '</td>';
                    echo '<td>';
                    echo '<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" data-id="' . $row['room_number'] . '" data-name="'. $row['room_number'] . '" data-name="' . $row['type'] . '" data-details="' . $row['available'] . '">Editar</button>';
                    echo ' <a href="?delete=' . $row['room_number'] . '." class="btn btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta reservación?\')">Eliminar</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo "<div class='alert alert-info' role='alert'>No se encontraron citas.</div>";
            }
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
                                <label for="type" class="form-label">Tipo:</label>
                                <select name="type" id="type" required class="form-control">
                                    <option value="0">Seleccione la habitación</option>
                                    <option value="1">Sencilla</option>
                                    <option value="2">Doble</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="available" class="form-label">Disponible:</label>
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

        <!-- Botón para regresar al login -->
        <div class="text-center mt-4">
            <a href="login.php" class="btn btn-secondary">Regresar al Login</a>
        </div>

        <!-- Modal para editar reservación -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Editar Reservación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="room_number" id="edit-id">
                            <div class="mb-3">
                                <label for="edit-room_type" class="form-label">Tipo de Habitación:</label>
                                <select name="type" id="edit-room_type" required class="form-control">
                                    <option value="0">Seleccione la habitación</option>
                                    <option value="1">Sencilla</option>
                                    <option value="2">Doble</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit-available" class="form-label">Disponible:</label>
                                <select name="available" id="edit-available" required class="form-control">
                                    <option value="1">Disponible</option>
                                    <option value="0">Ocupada</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" name="editRoom" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function showModal() {
                var modal = new bootstrap.Modal(document.getElementById('roomModal'));
                modal.show();
            }

            // Llenar el modal de edición con los datos de la cita
            const editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const date = button.getAttribute('data-date');
                const details = button.getAttribute('data-details');

                const modalTitle = editModal.querySelector('.modal-title');
                const editId = editModal.querySelector('#edit-id');
                const editPatientName = editModal.querySelector('#edit-patient_name');
                const editAppointmentDate = editModal.querySelector('#edit-appointment_date');
                const editDetails = editModal.querySelector('#edit-details');

                modalTitle.textContent = 'Editar Reservación: ' + name;
                editId.value = id;
                editPatientName.value = name;
                editAppointmentDate.value = date;
                editDetails.value = details;
            });
        </script>
    </body>
</html>

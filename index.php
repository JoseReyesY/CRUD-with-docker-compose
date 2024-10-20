<?php
session_start();

// Verificar si el usuario seleccionó un rol en login.php y almacenar en la sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['role'])) {
    $role = $_POST['role'];
    $_SESSION['role'] = $role;
} else {
    // Si ya existe un rol en la sesión, usarlo
    $role = $_SESSION['role'] ?? null;
}

// Redirigir al login si no se ha seleccionado un rol y no se está enviando una cita
if (!$role && !isset($_POST['submit'])) {
    // header('Location: loginF.php');
    //exit;
}

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Manejo de eliminación de citas
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $delete_query = "DELETE FROM reservations WHERE id = $id";
    mysqli_query($connect, $delete_query);
}

// Manejo de edición de citas - TODO
// if (isset($_POST['edit'])) {
//     $id = intval($_POST['id']);
//     $patient_name = mysqli_real_escape_string($connect, $_POST['patient_name']);
//     $appointment_date = mysqli_real_escape_string($connect, $_POST['appointment_date']);
//     $details = mysqli_real_escape_string($connect, $_POST['details']);

//     $update_query = "UPDATE appointments SET patient_name = '$patient_name', appointment_date = '$appointment_date', details = '$details' WHERE id = $id";
//     mysqli_query($connect, $update_query);
// }
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
    <?php if ($role == 'admin'): ?>
        <h2>Lista de Citas Registradas</h2>
        <?php
        $query = "SELECT * FROM appointments ORDER BY appointment_date ASC";
        $response = mysqli_query($connect, $query);

        if ($response) {
            echo '<table class="table table-bordered">';
            echo '<thead><tr><th>ID</th><th>Paciente</th><th>Fecha</th><th>Detalles</th><th>Acciones</th></tr></thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($response)) {
                echo '<tr>';
                echo '<td>' . $row['id'] . '</td>'; 
                echo '<td>' . $row['patient_name'] . '</td>';
                echo '<td>' . $row['appointment_date'] . '</td>';
                echo '<td>' . $row['details'] . '</td>';
                echo '<td>';
                echo '<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" data-id="' . $row['id'] . '" data-name="' . $row['patient_name'] . '" data-date="' . $row['appointment_date'] . '" data-details="' . $row['details'] . '">Editar</button>';
                echo ' <a href="?delete=' . $row['id'] . '" class="btn btn-danger" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta cita?\')">Eliminar</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo "<div class='alert alert-info' role='alert'>No se encontraron citas.</div>";
        }
        ?>
    <?php else: ?>
        <h2>Hacer una nueva reservación</h2>
        <form action="" method="post" class="mb-4">
            <!-- Nombre del huesped -->
            <div class="info--container">
                <div class="info-sub-container">
                    <label for="guest_name" class="form-label">Nombre:</label>
                    <input type="text" id="guest_name" name="guest_name" class="form-control" required>
                </div>

                <div class="info-sub-container">
                    <label for="guest_lastName" class="form-label">Apellido:</label>
                    <input type="text" id="guest_lastName" name="guest_lastName" class="form-control" required>
                </div>
            </div>
            
            <!-- Correo Electronico y telefono del huesped -->
            <div class="info--container">
                <div class="info-sub-container">
                    <label for="guest_email" class="form-label">Correo Electrónico:</label>
                    <input type="email" id="guest_email" name="guest_email" class="form-control" required>
                </div>

                <div class="info-sub-container">
                    <label for="guest_phone" class="form-label">Teléfono:</label>
                    <input type="number" id="guest_phone" name="guest_phone" class="form-control" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="room_type" class="form-label">Tipo de Habitación (sencilla o doble):</label>
                <input type="text" id="room_type" name="room_type" class="form-control" required>
            </div>

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

        <?php
        // Si el formulario fue enviado, registrar la nueva cita
        if (isset($_POST['submit'])) {
            $patient_name = mysqli_real_escape_string($connect, $_POST['patient_name']);
            $appointment_date = mysqli_real_escape_string($connect, $_POST['appointment_date']);
            $details = mysqli_real_escape_string($connect, $_POST['details']);

            $insert_query = "INSERT INTO appointments (patient_name, appointment_date, details) VALUES ('$patient_name', '$appointment_date', '$details')";

            if (mysqli_query($connect, $insert_query)) {
                echo "<div class='alert alert-success' role='alert'>Cita registrada correctamente.</div>";
            } else {
                echo "<div class='alert alert-danger' role='alert'>Error al registrar la cita: " . mysqli_error($connect) . "</div>";
            }
        }
        ?>
    <?php endif; ?>

    <!-- Botón para regresar al login -->
    <div class="text-center mt-4">
        <a href="login.php" class="btn btn-secondary">Regresar al Login</a>
    </div>
</div>

<!-- Modal para editar cita -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-patient_name" class="form-label">Nombre del Paciente:</label>
                        <input type="text" id="edit-patient_name" name="patient_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-appointment_date" class="form-label">Fecha de la Cita:</label>
                        <input type="date" id="edit-appointment_date" name="appointment_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-details" class="form-label">Detalles:</label>
                        <textarea id="edit-details" name="details" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" name="edit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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

        modalTitle.textContent = 'Editar Cita: ' + name;
        editId.value = id;
        editPatientName.value = name;
        editAppointmentDate.value = date;
        editDetails.value = details;
    });
</script>
</body>
</html>

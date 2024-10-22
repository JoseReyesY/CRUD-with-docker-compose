<?php
session_start();

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');
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
            margin-top: 40px;
            padding: 20px;
            display: flex;
            flex-direction: column;
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
            margin-bottom: 16px;
            color: #007bff;
        }

        li {
            margin-bottom: 16px;
        }

        a {
            text-decoration: none;
            color: black;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Reservaciones Hotel</h1>
    </header>

    <div class="container">
        <h2>Páginas de control</h2>
        <ul>
            <li><a href="roomsControl.php">Control de Habitaciones</a></li>
            <li><a href="reservationsControl.php">Control de Reservaciones</a></li>
        </ul>
    </div>
</body>
</html>

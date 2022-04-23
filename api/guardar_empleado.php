<?php

require "../config.php";

if (array_key_exists('dni', $_POST) && $_POST['dni'] !== '' && array_key_exists('nombre', $_POST) && $_POST['nombre'] !== '') {
    $dni = $_POST['dni'];
    $nombre = ucwords(strtolower($_POST['nombre']));

    if ($mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)) {
        if ($stmt = $mysqli->prepare("SELECT id FROM empleados WHERE dni = ?")) {
            $stmt->bind_param('i', $dni);
            $stmt->execute();
            $stmt->bind_result($id);
            if ($stmt->fetch()) {
                $stmt->close();
                header('Content-Type: application/json');
                echo json_encode("El numero de DNI ya esta asignado a otro empleado");
                exit();
            } else {
                $stmt->close();
                if ($stmt = $mysqli->prepare("INSERT INTO empleados(dni, nombre) values (?,?)")) {
                    $stmt->bind_param('is', $dni, $nombre);
                    $stmt->execute();
                    $empleado_id = $stmt->insert_id;
                    $stmt->close();
                    header('Content-Type: application/json');
                    echo json_encode("El empleado se creo correctamente");
                } else {
                    echo "error creando empleado " . $mysqli->error;
                }
            }
        } else {
            echo "error buscando empleado " . $mysqli->error;
        }



    }
} else {
    header('Content-Type: application/json');
    echo json_encode("No se estan recibiendo los datos de carga");
}


?>
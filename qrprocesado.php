<?php

require "./config.php";

if (array_key_exists('id', $_GET) && $_GET['id'] !== '' && array_key_exists('accion', $_GET) && $_GET['accion'] !== '' && array_key_exists('check', $_GET) && $_GET['check'] !== '') {
    $dni = $_GET['id'];
    $accion = $_GET['accion'];
    $check = $_GET['check'];
    if ($mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)) {
        if ($stmt = $mysqli->prepare("SELECT id, nombre, estado FROM empleados WHERE dni = ?")) {
            $stmt->bind_param('i', $dni);
            $stmt->execute();
            $stmt->bind_result($id, $nombre, $estado);
            if (!$stmt->fetch()) {
                echo "El DNI no se pudo encontrar en la DB!";
                exit();
            } else {
                $stmt->close();
                $fecha = date('Y-m-d');
                $md5 = md5($dni . $accion . $fecha);
                if ($check != $md5) {
                    echo "El DNI no se pudo encontrar en la DB!";
                    exit();
                }
            }
        }
        $timestamp = date('Y-m-d G:i:s');
        $estado = $accion == "entrada" ? 1 : 0;
        if ($stmt = $mysqli->prepare("UPDATE empleados SET estado=?, ultima_marcacion=? WHERE dni=?")) {
            $stmt->bind_param('isi', $estado, $timestamp, $dni);
            $stmt->execute();
            $stmt->close();
        }
        if ($accion == "entrada") {
            if ($stmt = $mysqli->prepare("INSERT INTO logs(empleado_id, fecha_entrada) values (?,?)")) {
                $stmt->bind_param('is', $id, $timestamp);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            if ($stmt = $mysqli->prepare("SELECT log_id, fecha_entrada FROM logs WHERE empleado_id = ? AND fecha_salida IS NULL ORDER BY fecha_entrada DESC")) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->bind_result($log_id, $fecha_entrada);
                if (!$stmt->fetch()) {
                    echo "No registra fecha de ingreso!";
                    exit();
                }
                $stmt->close();
            }
            $segundosTrabajados = strtotime($timestamp) - strtotime($fecha_entrada);
            if ($stmt = $mysqli->prepare("UPDATE logs SET fecha_salida=?, segundos_trabajados=? WHERE log_id=?")) {
                $stmt->bind_param('sii', $timestamp, $segundosTrabajados, $log_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcacion existosa</title>
</head>
<body onload="showInfo()">

</body>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showInfo() {
        Swal.fire({
            title: 'Marcacion Exitosa!',
            text: '<?php echo $nombre . " su " . $accion . " ha sido registrada!"?>',
            icon: 'success',
            confirmButtonColor: '#0d6efd'
        }).then(function(isConfirm) {
            window.close();
        });
    }
</script>
</html>


<?php
}
?>
<?php

//SELECT SEC_TO_TIME(SUM(`segundos_trabajados`)) as Tiempo_Trabajado FROM `logs` WHERE segundos_trabajados > 0 GROUP BY `empleado_id`

//SELECT SUM(foo), DATE(mydate) FROM a_table GROUP BY DATE(a_table.mydate)

// por cada empleado
// SELECT SEC_TO_TIME(SUM(`segundos_trabajados`)) as Tiempo_Trabajado, nombre, DATE(fecha_entrada) fecha FROM `logs` LEFT JOIN empleados ON logs.empleado_id = empleados.id WHERE segundos_trabajados > 0 AND `empleado_id` = 1 GROUP BY DATE(fecha_entrada)

// por cada empleado agrupado por cada dia
//SELECT SEC_TO_TIME(SUM(segundos_trabajados)) as Tiempo_Trabajado, DATE(fecha_entrada) FROM logs LEFT JOIN empleados ON logs.empleado_id = empleados.id WHERE segundos_trabajados > 0 AND DATE(fecha_entrada) >= '2022-04-25' AND DATE(fecha_salida) <= '2022-04-25' AND empleado_id = 1 GROUP BY DATE(fecha_entrada)

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require "../config.php";
  

if (array_key_exists('desde', $_POST) && $_POST['desde'] !== '' && array_key_exists('hasta', $_POST) && $_POST['hasta'] !== '') {
    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];

    if ($hasta >= $desde) {
        
        if ($mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('A1', 'Nombre');
            $fila = 1;
            if ($stmt = $mysqli->prepare("SELECT SEC_TO_TIME(SUM(segundos_trabajados)) as Tiempo_Trabajado, nombre, id FROM logs LEFT JOIN empleados ON logs.empleado_id = empleados.id WHERE segundos_trabajados > 0 AND DATE(fecha_entrada) >= ? AND DATE(fecha_salida) <= ? GROUP BY DATE(fecha_entrada)")) {
                $stmt->bind_param('ss', $desde, $hasta);
                $stmt->execute();
                $stmt->bind_result($segundos, $nombre, $id);
                while ($stmt->fetch()){
                    $celda = 'A' . $fila;
                    $sheet->setCellValue($celda, $nombre);
                    $celda = 'B' . $fila;
                    $sheet->setCellValue($celda, $id);
                    $fila++;
                }
                $stmt->close();
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('informe.xlsx');
            exit();

        }



        
        
        // $sheet->setCellValue('A2', $dates[0]);

        

        // for ($i = 0; $i < count($dates); $i++) {
        //     echo $dates[i];
        // }
    }
}



?>
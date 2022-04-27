<?php

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

            $sheet->setCellValue('A1', 'Nombre');
            $sheet->setCellValue('B1', 'Total Trabajado');

            if ($stmt = $mysqli->prepare("SELECT SEC_TO_TIME(SUM(segundos_trabajados)) as Tiempo_Trabajado, DATE(fecha_entrada) fecha FROM logs LEFT JOIN empleados ON logs.empleado_id = empleados.id WHERE segundos_trabajados > 0 AND DATE(fecha_entrada) >= ? AND DATE(fecha_salida) <= ? GROUP BY fecha ORDER BY fecha")) {
                $stmt->bind_param('ss', $desde, $hasta);
                $stmt->execute();
                $stmt->bind_result($tiempoTrabajadoDiario, $fecha);
                while ($stmt->fetch()){
                    $dias[] = [
                        'fecha' => $fecha
                        ];
                }
                $stmt->close();
                $columna = 67;
                foreach($dias as $dia) {
                    $celda = chr($columna) . '1';
                    $sheet->setCellValue($celda, $dia['fecha']);
                    $columna++;
                }
            }

            $fila = 2;
            if ($stmt = $mysqli->prepare("SELECT SEC_TO_TIME(SUM(segundos_trabajados)) as Tiempo_Trabajado, nombre, id FROM logs LEFT JOIN empleados ON logs.empleado_id = empleados.id WHERE segundos_trabajados > 0 AND DATE(fecha_entrada) >= ? AND DATE(fecha_salida) <= ? GROUP BY empleado_id")) {
                $stmt->bind_param('ss', $desde, $hasta);
                $stmt->execute();
                $stmt->bind_result($tiempoTrabajadoTotal, $nombre, $id);
                while ($stmt->fetch()){
                    $respuesta[] = [
                        'id' => $id, 'nombre' => $nombre, 'total' => $tiempoTrabajadoTotal
                      ];
                }
                $stmt->close();
                foreach($respuesta as $empleado) {
                    $columna = 65;
                    $celda = chr($columna) . $fila;
                    $sheet->setCellValue($celda, $empleado['nombre']);
                    $columna++;
                    $celda = chr($columna) . $fila;
                    $sheet->setCellValue($celda, $empleado['total']);

                    if ($stmt = $mysqli->prepare("SELECT SEC_TO_TIME(SUM(segundos_trabajados)) as Tiempo_Trabajado, DATE(fecha_entrada) fecha FROM logs LEFT JOIN empleados ON logs.empleado_id = empleados.id WHERE segundos_trabajados > 0 AND DATE(fecha_entrada) >= ? AND DATE(fecha_salida) <= ? AND empleado_id = ? GROUP BY DATE(fecha_entrada) ORDER BY fecha")) {
                        $stmt->bind_param('ssi', $desde, $hasta, $empleado['id']);
                        $stmt->execute();
                        $stmt->bind_result($tiempoTrabajadoDiario, $fecha);
                        while ($stmt->fetch()){
                            $detalleDias[] = [
                                'fecha' => $fecha, 'total' => $tiempoTrabajadoDiario
                              ];
                        }
                        $stmt->close();

                        foreach($detalleDias as $detalle) {
                            for ($i = 'C'; $i <= $sheet->getHighestColumn(); $i++) {
                                $celda = $i . '1';
                                $cellValue = $sheet->getCell($celda)->getValue();
                                if ($cellValue == $detalle['fecha']){
                                    $celda = $i . $fila;
                                    $sheet->setCellValue($celda, $detalle['total']);
                                    break;
                                }
                            }
                        }
                        unset($detalleDias);
                    }
                    $fila++;
                }
            }

            for ($i = 'A'; $i <= $sheet->getHighestColumn(); $i++) {
                $sheet->getColumnDimension($i)->setAutoSize(true);
                $cell = $i . '1';
                $sheet->getStyle($cell)->getFont()->setBold(true);
                if ($i > 'A') $sheet->getStyle($i)->getAlignment()->setHorizontal('right');
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('informe.xlsx');

            // if(file_exists($filename)) {

            //     //Define header information
            //     header('Content-disposition: attachment; filename='.$filename);
            //     header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            //     header('Content-Length: ' . filesize($filename));
            //     header('Content-Transfer-Encoding: binary');
            //     header('Cache-Control: must-revalidate');
            //     header('Pragma: public');
            //     ob_clean();
            //     flush(); 
            //     readfile($filename);

            // }
        }
    }
}



?>
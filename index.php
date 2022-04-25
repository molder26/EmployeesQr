<?php
$inicio = 'active';

require "./config.php";
require "./header.php";

if ($mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)) {
    $resource_empleados = $mysqli->query("SELECT * FROM empleados WHERE borrado IS NULL ORDER BY nombre");
}
?>
<style>
    #nuevo_empleado {
        display: flex;
        margin-left: 10px;
    }
    #inputs {
        padding: 1.5rem;
        margin-right: 20px;
        margin-left: 20px;
        border-width: 1px;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
        /* margin: 1rem -0.75rem 0; */
        border: solid 1px #dee2e6;
    }
    #contenedor {
        height: 90%;
        width: 80%;
    }
</style>

<div>
    <button type="button" name="nuevo_empleado" id="nuevo_empleado" class="btn btn-primary" btn-lg btn-block" onclick="nuevo_empleado()">Nuevo Empleado</button>
</div>

<table class="table" id="tabla">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col" style="text-align: center">Nombre</th>
      <th scope="col" style="text-align: center">Dni</th>
      <th scope="col" style="text-align: center">Ultima Marcacion</th>
      <th scope="col">Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php while ( $row = $resource_empleados->fetch_assoc() ) { ?>
        <tr>
            <th scope="row" style="vertical-align:middle"><?php echo $row['id']; ?></th>
            <?php
            if ($row['estado'] == 0){
                $color = "#FFFFFF";
                $iconMarcacion = "fas fa-utensils";
                $btnClass = "btn-success";
            } else {
                $color = "#198754";
                $iconMarcacion = "fas fa-sign-out-alt";
                $btnClass = "btn-danger";
            }
            ?>
            <td style="vertical-align:middle">
                <div id="contenedor" style="text-align: center; background-color:<?php echo $color; ?>">
                    <span><?php echo $row['nombre']; ?></span>
                </div>
            </td>
            <td style="text-align: center; vertical-align:middle"><?php echo $row['dni']; ?></td>
            <td style="text-align: center; vertical-align:middle"><?php echo $row['ultima_marcacion']; ?></td>
            <td style="vertical-align:middle">
                <!-- <div class='btn-group' role='group' aria-label='Basic example'>
                    <button data-toggle="modal" data-target="#editModal" type="button" class="btn btn-success" onclick="showModal()"><i class="fas fa-edit"></i></button>
                </div> -->
                <div class="btn-group" role="group" aria-label="Basic example">
                    <div class="btn-group me-2" role="group" aria-label="First group">
                        <button type="button" class="btn <?php echo $btnClass; ?> btn-md" onclick=showQr(<?php echo $row['estado'] . "," . $row['id'] ?>)><i class="<?php echo $iconMarcacion; ?>"></i></button>
                    </div>
                    <!-- <button type="button" class="btn btn-secondary btn-md">Middle</button> -->
                </div>
            </td>
        </tr>
    <?php } ?>
  </tbody>
</table>

<div id="inputs" style="display:none">
    <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon1">Nombre</span>
        <input type="text" class="form-control" id="nombre" placeholder="Nombre Completo" aria-label="Username" aria-describedby="basic-addon1">
    </div>
    <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon1">DNI</span>
        <input type="text" class="form-control" id="dni" placeholder="DNI" aria-label="dni" aria-describedby="basic-addon1">
    </div>
    <div>
        <button type="button" name="guardar_empleado" id="guardar_empleado" class="btn btn-success" btn-lg btn-block" onclick="guardar_empleado()">Guardar</button>
        <button type="button" name="cancelar_empleado" id="cancelar_empleado" class="btn btn-danger" btn-lg btn-block" onclick="cancelar_empleado()">Cancelar</button>
    </div>
</div>

<!-- Modal -->
<!-- Small modal -->
<div id="QrModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <center><h4 class="modal-title" id="mySmallModalLabel">Escanear este QR</h4></center>
            
        </div>
        <div class="modal-body">
            <center><img src="" alt="" width="251" height="297" id="imageqr"></center>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="location.reload();">Cerrar</button>
        </div>
        </div>
    </div>
    </div>
</div>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script>
    function nuevo_empleado() {
        document.getElementById('nuevo_empleado').style.display = "none";
        document.getElementById('tabla').style.display = "none";
        document.getElementById('inputs').style.display = "";
    }

    function cancelar_empleado() {
        location.reload();
    }

    function guardar_empleado() {
        let dni=document.querySelector('#dni').value;
        let nombre=document.querySelector('#nombre').value;

        if (dni.trim().length === 0 || nombre.trim().length === 0){
            Swal.fire({
                title: 'Error!',
                text: 'Los campos no pueden estar vacios!',
                icon: 'error',
                confirmButtonColor: '#0d6efd'
            });
            return false;
        }

        $.post("./api/guardar_empleado.php", {
            dni:dni,
            nombre:nombre
            },function(data){
                if(data == "El empleado se creo correctamente"){
                    Swal.fire({
                        title: 'Exito!',
                        text: 'Empleado guardado con exito',
                        icon: 'success',
                        confirmButtonColor: '#0d6efd'
                    }).then(function(isConfirm) {
                        location.reload();
                    });
                }else{
                    Swal.fire({
                        title: 'Error!',
                        text: data,
                        icon: 'error',
                        confirmButtonColor: '#0d6efd'
                    });
                }
        });
    }

    function showQr(estado, id) {
        let url = window.location.protocol + "//" + window.location.host;
        condicion = estado === 0 ? 'entrada' : 'salida';

        $.post("./api/qr.php", {
            id:id,
            condicion:condicion,
            url:url
            },function(data){
                if(data == "Qr generado correctamente"){
                    let imagen = "./api/qr.png";
					refreshImage("imageqr", imagen);
                    $("#QrModal").modal("show");
                }else{
                    Swal.fire({
                        title: 'Error!',
                        text: data,
                        icon: 'error',
                        confirmButtonColor: '#0d6efd'
                    });
                }
        });
    }

    function refreshImage(imgElement, imgURL){    
        // create a new timestamp 
        let timestamp = new Date().getTime();  
        let el = document.getElementById(imgElement);  
        let queryString = "?t=" + timestamp;    
        el.src = imgURL + queryString;    
    }
</script>
</html>
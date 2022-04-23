<?php
$empleados = 'active';

require "./config.php";
require "./header.php";

if ($mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)) {
    $resource_empleados = $mysqli->query("SELECT * FROM empleados ORDER BY nombre");
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
</style>

<div>
    <button type="button" name="nuevo_empleado" id="nuevo_empleado" class="btn btn-primary" btn-lg btn-block" onclick="nuevo_empleado()">Nuevo Empleado</button>
</div>

<table class="table" id="tabla">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Nombre</th>
      <th scope="col">Dni</th>
    </tr>
  </thead>
  <tbody>
    <?php while ( $row = $resource_empleados->fetch_assoc() ) { ?>
        <tr>
            <th scope="row"><?php echo $row['id']; ?></th>
            <td><?php echo $row['nombre']; ?></td>
            <td><?php echo $row['dni']; ?></td>
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
                confirmButtonColor: '#dc3545'
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
                        confirmButtonColor: '#157347'
                    }).then(function(isConfirm) {
                        location.reload();
                    });
                }else{
                    Swal.fire({
                        title: 'Error!',
                        text: data,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
        });
        
    }
</script>
</html>
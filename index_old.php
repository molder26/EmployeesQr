<?php
$inicio = 'active';

require "./header.php";
require "./config.php";

?>

<style>
    .card {
        display: flex;
        margin-right: 5px;
        margin-left: 5px;
        height: 150%;
    }
    #recuadro {
        padding: 1.5rem;
        margin-right: 20px;
        margin-left: 20px;
        border-width: 1px;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
        /* margin: 1rem -0.75rem 0; */
        border: solid 1px #dee2e6;
        height: 280px;
    }
    .form-select {
        margin-bottom: 10px;
    }
    .horizontal-center {
        position: absolute;
        margin-top: 20px;
        /* left: 30%; */
        margin-left: 30%;
    }
    .parent{
        position: relative;
    }
    #btnVolver{
        margin-top: 450px;
        margin-left: 40%;
    }
</style>
<div id="recuadro">
    <div class="row">
    <div class="col-sm-6">
        <div class="card">
        <div class="card-body">
            <h5 class="card-title">Marcar Entrada</h5>
            <div>
                <select class="form-select" aria-label="Default select example" id="selectEntrada" onchange="checkSelects()">
                    <option value="-1" selected>Empleado</option>
                    <?php
                        if ($mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)) {
                            $resource_barrios = $mysqli->query("SELECT * FROM empleados WHERE borrado IS NULL ORDER BY nombre");
                            while ( $row = $resource_barrios->fetch_assoc() ) {
                    ?>
                                <option value="<?php echo $row['dni']; ?>"><?php echo $row['nombre']; ?></option>
					    <?php }} ?>
                </select>
            </div>
            <button type="button" class="btn btn-md btn-primary" disabled id="btnEntrada" onclick="generarQr('entrada')">Entrada</button>
        </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card">
        <div class="card-body">
            <h5 class="card-title">Marcar Salida</h5>
            <select class="form-select" aria-label="Default select example" id="selectSalida" onchange="checkSelects()">
                <option value="-1" selected>Empleado</option>
                    <?php
                        if ($mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE)) {
                            $resource_barrios = $mysqli->query("SELECT * FROM empleados WHERE borrado IS NULL ORDER BY nombre");
                            while ( $row = $resource_barrios->fetch_assoc() ) {
                    ?>
                                <option value="<?php echo $row['dni']; ?>"><?php echo $row['nombre']; ?></option>
					    <?php }} ?>
                </select>
            <button type="button" class="btn btn-md btn-primary" disabled id="btnSalida" onclick="generarQr('salida')">Salida</button>
        </div>
        </div>
    </div>
    </div>
</div>

<div id="qr" class="parent" style="display:none">
    <img id='imageqr' class="horizontal-center">
    <button type="button" name="btnVolver" id="btnVolver" class="btn btn-primary btn-lg btn-block" onclick="location.reload();">Volver</button>
</div>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script>
    function checkSelects() {
        let selectEntrada = document.getElementById("selectEntrada").value;
        let selectSalida = document.getElementById("selectSalida").value;
        let btnEntrada = document.getElementById("btnEntrada");
        let btnSalida = document.getElementById("btnSalida");
        btnEntrada.disabled = selectEntrada === "-1";
        btnSalida.disabled = selectSalida === "-1";
    }

    function generarQr(condicion) {
        document.getElementById('recuadro').style.display = "none";

        let selectSelected;
        let dni;
        let nombre;
        let url = window.location.protocol + "//" + window.location.host;

        if (condicion === 'entrada'){
            selectSelected = document.getElementById("selectEntrada");
        } else {
            selectSelected = document.getElementById("selectSalida");
        }

        dni = selectSelected.value;
        nombre = selectSelected.options[selectSelected.selectedIndex].text;

        console.log(dni);
        console.log(nombre);

        $.post("./api/qr.php", {
            dni:dni,
            nombre:nombre,
            condicion:condicion,
            url:url
            },function(data){
                if(data == "Qr generado correctamente"){
                    let imagen = "../api/qr.png";
					refreshImage("imageqr", imagen);
                }else{
                    Swal.fire({
                        title: 'Error!',
                        text: data,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
        });
        document.getElementById('qr').style.display = "";
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

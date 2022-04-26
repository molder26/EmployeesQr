<?php
$informe = 'active';

// require "./config.php";
require "./header.php";

$fecha = date('Y-m-d');

?>

<style>
    .card {
        display: flex; 
        margin-top: 30px;
        margin-left: 30px;
        height: 300px;
        width: 90%;
        /* margin-right: 120px; */
    }
    label {
        margin-top: 20px;
        margin-left: 100px;
    }
    #btnGenerar {
        margin-top: 50px;
        margin-left: 200px;

    }
</style>


<div class="card shadow">
  <div class="card-header">
    Parametros del informe
  </div>
  <div class="card-body">
    <div>
        <label for="desde">Desde:</label>
        <input type="date" id="desde" name="trip-start"
                value="<?php echo $fecha; ?>"
                min="2021-01-01">
    </div>
    
    <div>
        <label for="hasta">Hasta:</label>
        <input type="date" id="hasta" name="trip-start"
                value="<?php echo $fecha; ?>"
                min="2021-01-01">
    </div>
    <button type="button" name="" id="btnGenerar" class="btn btn-primary" btn-lg btn-block" onclick="generarInforme()">Generar</button>
  </div>
</div>


</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script>
    function generarInforme() {
        let desde = document.querySelector('#desde').value;
        let hasta = document.querySelector('#hasta').value;

        $.post("./api/getInforme.php", {
            desde:desde,
            hasta:hasta
            },function(data){
                console.table(data);
        });
    }
</script>

</html>
<?php
session_start();

if(!isset($_SESSION["rol"]) || $_SESSION["rol"]!=="maestro"){
    header("Location:index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title>Panel Docente</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

</head>

<body class="bg-light p-4">

<div class="container">

<h3>Panel Docente</h3>

<button class="btn btn-primary" onclick="generarQR()">
Generar QR
</button>

<hr>

<div id="contenedorQR" class="p-3 bg-white"></div>

<p id="fechaQR"></p>

</div>


<!-- MODAL -->
<div class="modal fade" id="modalQR">
<div class="modal-dialog modal-dialog-centered">

<div class="modal-content p-4 text-center">

<h5>Escanea</h5>

<div id="qrModal"></div>

<button class="btn btn-secondary mt-3" data-bs-dismiss="modal">
Cerrar
</button>

</div>

</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script>

async function generarQR(){

    const cont = document.getElementById("qrModal");
    cont.innerHTML = "Cargando...";

    try{

        const res = await fetch("api/generar_qr.php");
        const data = await res.json();

        if(data.error){
            alert(data.error);
            return;
        }

        cont.innerHTML = "";

        const url =
            window.location.origin+
            "procesar_qr.php?token="+data.token;

        new QRCode(cont,{
            text:url,
            width:220,
            height:220
        });

        document.getElementById("fechaQR").innerText =
            "Generado: "+new Date().toLocaleString();

        const modal = new bootstrap.Modal(
            document.getElementById("modalQR")
        );

        modal.show();


    }catch(err){

        console.error(err);
        alert("Error");

    }

}

</script>

</body>
</html>
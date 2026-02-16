<?php
session_start();

if (!isset($_SESSION["pendiente_verificacion"])) {
    header("Location: index.html");
    exit;
}

$nombre = $_SESSION["nombre_email"] ?? "";
$correo = $_SESSION["correo_email"] ?? "";
$codigo = $_SESSION["codigo_email"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title>Verificación</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<!-- EmailJS -->
<script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>

<script>
(function(){
    emailjs.init("aYQj8l4hubsf4dk3f"); // TU PUBLIC KEY
})();
</script>

<style>

*{
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    margin:0;
    min-height:100vh;
    background:linear-gradient(135deg,#004a99,#007bff);
    display:flex;
    justify-content:center;
    align-items:center;
}

.box{
    background:white;
    width:100%;
    max-width:400px;
    padding:2.5rem;
    border-radius:20px;
    box-shadow:0 15px 35px rgba(0,0,0,.25);
    text-align:center;
}

.box h2{
    color:#004a99;
}

.box p{
    color:#6c757d;
    margin-bottom:25px;
}

.box input{
    width:100%;
    padding:12px;
    font-size:1.3rem;
    letter-spacing:6px;
    text-align:center;
    border-radius:10px;
    border:1px solid #ced4da;
}

.box button{
    margin-top:15px;
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    color:white;
    background:#007bff;
    cursor:pointer;
}

.box button:hover{
    background:#0056b3;
}

.green{
    color:green;
    margin-bottom:10px;
}

</style>
</head>

<body>

<div class="box">

<?php if(isset($_GET["ok"])): ?>
<p class="green">Código enviado correctamente ✔</p>
<?php endif; ?>

<h2>Verificación</h2>

<p>Revisa tu correo e ingresa el código</p>


<!-- FORM VERIFICAR -->
<form action="verificar_codigo.php" method="POST">

<input type="text"
       name="codigo"
       maxlength="6"
       required
       placeholder="123456">

<button type="submit">Verificar</button>

</form>


<!-- BOTÓN EMAILJS -->
<button onclick="enviarCorreo()" style="background:#28a745;">
Enviar / Reenviar código
</button>

</div>


<script>

function enviarCorreo(){

    const params = {
        user_name: "<?php echo $nombre ?>",
        verification_code: "<?php echo $codigo ?>",
        to_email: "<?php echo $correo ?>"   // DESTINO
    };

    emailjs.send(
        "service_z2iq85g",      // SERVICE
        "template_um7o5c8",     // TEMPLATE
        params
    ).then(function(){

        window.location = "esperar_codigo.php?ok=1";

    }, function(error){

        alert("Error enviando: " + error.text);

    });
}

</script>

</body>
</html>
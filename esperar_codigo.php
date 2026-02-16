<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["pendiente_verificacion"])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION["pendiente_verificacion"];


/* BUSCAR USUARIO */

$sql = "SELECT nombre, correo FROM usuarios WHERE id = ? LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){
    die("Usuario no encontrado");
}

$nombre = $user["nombre"];
$correo = $user["correo"];
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
    emailjs.init("aYQj8l4hubsf4dk3f");
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

.resend{
    background:#28a745;
}

.resend:hover{
    background:#1e7e34;
}

</style>
</head>

<body>

<div class="box">

<?php if(isset($_GET["ok"])): ?>
<p class="green">Código enviado ✔</p>
<?php endif; ?>

<h2>Verificación</h2>

<p>Revisa tu correo</p>


<!-- VERIFICAR -->

<form action="verificar_codigo.php" method="POST">

<input type="text"
       name="codigo"
       maxlength="6"
       required
       placeholder="123456">

<button type="submit">Verificar</button>

</form>


<!-- REENVIAR -->

<button onclick="reenviarCodigo()" class="resend">
Reenviar código
</button>


</div>


<script>

function reenviarCodigo(){

    fetch("enviar_codigo.php")
    .then(r => r.text())
    .then(res => {

        if(res !== "OK"){
            alert("Error generando código");
            return;
        }

        const params = {
            user_name: "<?php echo $nombre ?>",
            to_email: "<?php echo $correo ?>",
            verification_code: "Nuevo código enviado"
        };

        emailjs.send(
            "service_z2iq85g",
            "template_um7o5c8",
            params
        ).then(()=>{

            window.location = "esperar_codigo.php?ok=1";

        }).catch(error=>{

            alert("Error EmailJS: " + error.text);

        });

    });

}

</script>

</body>
</html>
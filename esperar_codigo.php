<?php
session_start();

if (!isset($_SESSION["pendiente_verificacion"])) {
    header("Location: index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title>Verificación</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

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


<!-- REENVIAR (BACKEND) -->
<form action="enviar_codigo.php" method="POST">

<button type="submit" class="resend">
Reenviar código
</button>

</form>


</div>

</body>
</html>
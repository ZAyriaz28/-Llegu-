<?php
session_start();

if(!isset($_SESSION["pendiente_verificacion"])){
    header("Location: index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Verificación</title>

<!-- Fuente moderna -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>

/* RESET */
*{
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}


/* FONDO */
body{
    margin:0;
    min-height:100vh;

    background: linear-gradient(135deg, #004a99 0%, #007bff 100%);

    display:flex;
    justify-content:center;
    align-items:center;
}


/* CAJA */
.box{

    background: rgba(255,255,255,0.97);

    width:100%;
    max-width:400px;

    padding:2.5rem;

    border-radius:20px;

    box-shadow:0 15px 35px rgba(0,0,0,.25);

    text-align:center;
}


/* TITULO */
.box h2{
    color:#004a99;
    font-size:1.8rem;
    font-weight:600;
    margin-bottom:10px;
}


/* TEXTO */
.box p{
    color:#6c757d;
    font-size:0.95rem;
    margin-bottom:25px;
}


/* INPUT */
.box input{

    width:100%;

    padding:12px;

    font-size:1.3rem;

    letter-spacing:6px;

    text-align:center;

    border-radius:10px;

    border:1px solid #ced4da;

    outline:none;

    transition:0.3s;
}


/* FOCO */
.box input:focus{

    border-color:#007bff;

    box-shadow:0 0 0 3px rgba(0,123,255,.25);
}


/* BOTON */
.box button{

    margin-top:20px;

    width:100%;

    padding:12px;

    background:#007bff;

    border:none;

    border-radius:10px;

    color:white;

    font-size:1rem;

    font-weight:500;

    cursor:pointer;

    transition:0.3s;
}


/* HOVER BOTON */
.box button:hover{

    background:#0056b3;

    transform:translateY(-1px);
}

</style>
</head>

<body>

<div class="box">

    <h2>Verificación</h2>

    <p>Revisa tu correo e ingresa el código de 6 dígitos</p>

    <form action="verificar_codigo.php" method="POST">

        <input 
            type="text" 
            name="codigo"
            maxlength="6"
            required
            placeholder="123456"
            autocomplete="one-time-code"
        >

        <button type="submit">Verificar</button>

    </form>

</div>

</body>
</html>

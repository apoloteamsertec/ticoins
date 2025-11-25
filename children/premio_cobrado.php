<?php
session_start();
if (!isset($_SESSION["child_id"])) {
    header("Location: login.php");
    exit;
}

// Obtener premio desde GET
$premio = isset($_GET["premio"]) ? htmlspecialchars($_GET["premio"]) : "Premio";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Premio Canjeado</title>
<link rel="stylesheet" href="css/children.css">
<style>
/* Caja principal */
.success-container{
    max-width:480px;
    margin:60px auto;
    text-align:center;
    padding:30px;
}

/* Icono verde */
.success-icon{
    font-size:72px;
    color:#3EB04B;
    margin-bottom:10px;
}

/* Texto principal */
.success-title{
    font-size:24px;
    font-weight:700;
    color:#2e7a35;
    margin-bottom:10px;
}

/* Nombre del premio */
.success-premio{
    font-size:18px;
    font-weight:600;
    color:#444;
    margin-bottom:20px;
}

/* Botón volver */
.btn-volver{
    display:inline-block;
    background:#3EB04B;
    color:white;
    padding:12px 24px;
    border-radius:12px;
    text-decoration:none;
    font-size:16px;
    font-weight:600;
    box-shadow:0 5px 15px rgba(0,0,0,0.25);
}

/* Confeti simple */
.confetti{
    position:fixed;
    top:-10px;
    width:10px;
    height:10px;
    background:#ffcc00;
    opacity:0.8;
    animation:fall linear infinite;
}

@keyframes fall{
    0%{ transform:translateY(0) rotate(0); }
    100%{ transform:translateY(110vh) rotate(360deg); }
}
</style>
</head>

<body>

<div class="success-container">

    <div class="success-icon">🎉</div>

    <div class="success-title">¡Premio Canjeado!</div>

    <div class="success-premio">
        Canjeaste: <strong><?= $premio ?></strong>
    </div>

    <a href="index.php" class="btn-volver">Volver al Panel</a>

</div>

<!-- CONFETI -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    for(let i = 0; i < 25; i++){
        let c = document.createElement("div");
        c.classList.add("confetti");
        
        // colores aleatorios
        const colores = ["#ff3b30","#ffcc00","#3EB04B","#0070c9","#ff8a00"];
        c.style.background = colores[Math.floor(Math.random()*colores.length)];

        // posición inicial
        c.style.left = Math.random()*100 + "vw";
        c.style.animationDuration = (3 + Math.random()*3) + "s";

        document.body.appendChild(c);
    }
});
</script>

</body>
</html>

<?php
session_start();

// Si entra sin premio, volver al inicio
if (!isset($_GET["premio"])) {
    header("Location: index.php");
    exit;
}

$premio = htmlspecialchars($_GET["premio"]);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Premio canjeado!</title>
    <link rel="stylesheet" href="css/children.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        .cobrado-container {
            text-align: center;
            margin-top: 40px;
            padding: 25px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            width: 90%;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }

        .cobrado-titulo {
            font-size: 26px;
            font-weight: 700;
            color: #0A8A0F;
            margin-bottom: 10px;
        }

        .cobrado-premio {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin-top: 10px;
        }

        .check-icon {
            font-size: 70px;
            color: #3EB04B;
            margin-bottom: 5px;
            animation: pop .4s ease-out;
        }

        @keyframes pop {
            0% { transform: scale(0.4); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .btn-volver {
            display: block;
            margin-top: 28px;
            background: #3EB04B;
            color: white;
            padding: 14px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-volver:hover {
            background: #2d8f3c;
        }
    </style>
</head>

<body>

<header class="topbar-child">
    <div class="hola">
        🎉 ¡PREMIO CANJEADO!
    </div>
</header>

<main class="child-page">

    <div class="cobrado-container">

        <div class="check-icon">✔</div>

        <div class="cobrado-titulo">¡Felicidades!</div>

        <p class="cobrado-premio">
            Canjeaste el premio:<br>
            <strong><?= $premio ?></strong>
        </p>

        <a href="index.php" class="btn-volver">Volver al inicio</a>

    </div>

</main>

</body>
</html>

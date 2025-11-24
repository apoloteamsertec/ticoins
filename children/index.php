<?php
session_start();
require_once "../config/supabase.php";

/* 🔹 IMPORTANTE:
   El Login para niños lo agregaremos después.
   Por ahora vamos a simular que el niño está logueado.
*/

// EJEMPLO — luego lo traeremos de Supabase
$usuario_id   = "e373a04f-156b-4fc8-98d7-b6ea739d939e"; 
$nombre_nino  = "Javier Ortega";
$username     = "stitch";

// Coins totales
$perfil = $supabase->from("profiles", "GET", null, "id=eq.$usuario_id");
$coins = isset($perfil[0]["coins"]) ? $perfil[0]["coins"] : 0;

// Tareas asignadas (solo activas)
$tareas = $supabase->from("tareas", "GET", null, "activa=eq.true");

//FILTRAR TAREAS PARA ESTE NIÑO
$tareas_filtradas = [];
if ($tareas) {
    foreach ($tareas as $t) {
        // tipo_asignacion: "individual" o "general"
        if ($t["tipo_asignacion"] === "general") {
            $tareas_filtradas[] = $t;
        }
        else if ($t["tipo_asignacion"] === "individual" && $t["usuario_asignado"] === $usuario_id) {
            $tareas_filtradas[] = $t;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Ti-Coins</title>
<link rel="stylesheet" href="css/children.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>

<!-- ==============================
     BARRA SUPERIOR DEL NIÑO
==================================-->
<header class="topbar-child">
    <div class="hola">
        HOLA <span>@<?= strtoupper(htmlspecialchars($username)) ?></span>
    </div>

    <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</header>


<!-- ==============================
     CONTENIDO CENTRAL
==================================-->
<main class="child-page">

    <!-- PILLS DE COINS -->
    <div class="coins-pill">
        Tenés <span><?= str_pad($coins, 5, "0", STR_PAD_LEFT) ?></span> Ti-Coins
    </div>


    <!-- TARJETA DE TAREAS DISPONIBLES -->
    <section class="card-tareas">
        <div class="card-tareas-header">
            Tareas Disponibles
        </div>

        <div class="card-tareas-body">

            <?php if(empty($tareas_filtradas)): ?>
                <p class="sin-tareas">
                    Por ahora no tenés tareas asignadas 😴
                </p>
            <?php else: ?>

                <?php foreach ($tareas_filtradas as $i=>$tarea): ?>
                <div class="tarea-item">

                    <div class="tarea-titulo">
                        <?= htmlspecialchars($tarea["titulo"]) ?>
                    </div>

                    <div class="tarea-coins">
                        <?= (int)$tarea["coins_valor"] ?> TI-Coins
                    </div>

                    <a href="tarea_realizar.php?id=<?= $tarea["id"] ?>"
                       class="tarea-btn">
                        Realizar
                    </a>

                </div>

                <?php if($i < count($tareas_filtradas)-1): ?>
                    <div class="tarea-separator"></div>
                <?php endif; ?>

                <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </section>

    <p class="mensaje-amor">
        No te olvides nunca que tus tíos te aman mucho 💚
    </p>

</main>

</body>
</html>

<?php
require "../auth/check_admin.php";
require "../config/supabase.php";

$id = $_GET["id"];       // id de tareas_realizadas
$accion = $_GET["accion"];

// obtener registro
$registro = $supabase->from("tareas_realizadas", "GET", null, "id=eq.$id")[0];

$idUsuario = $registro["usuario_id"];
$idTarea   = $registro["tarea_id"];

// obtener valor de la tarea
$tarea = $supabase->from("tareas", "GET", null, "id=eq.$idTarea")[0];
$valorCoins = $tarea["coins_valor"];

if ($accion === "aprobar") {

    // 1. Cambiar estado
    $supabase->from("tareas_realizadas", "PATCH", [
        "estado" => "aprobada",
        "coins_otorgados" => $valorCoins,
        "fecha_resolucion" => date("c")
    ], "id=eq.$id");

    // 2. Sumar coins al perfil
    $supabase->from("profiles", "PATCH", [
        "coins" => $supabase->from("profiles", "GET", null, "id=eq.$idUsuario")[0]["coins"] + $valorCoins
    ], "id=eq.$idUsuario");

    // 3. Registrar en coin_movimientos
    $supabase->from("coins_movimientos", "POST", [
        "usuario_id" => $idUsuario,
        "tipo" => "tarea",
        "monto" => $valorCoins,
        "descripcion" => "Aprobación de tarea: {$tarea['titulo']}",
        "referencia" => $id
    ]);

} else {

    // Rechazar
    $supabase->from("tareas_realizadas", "PATCH", [
        "estado" => "rechazada",
        "fecha_resolucion" => date("c")
    ], "id=eq.$id");
}

header("Location: revisar.php");
exit;

<?php
require_once "../auth/check_admin.php";
require_once "../config/supabase.php";

$id = $_GET["id"];
$accion = $_GET["accion"];

// obtener registro
$registro = $supabase->from("tareas_realizadas", "GET", null, "id=eq.$id")[0];

$idUsuario = $registro["usuario_id"];
$idTarea   = $registro["tarea_id"];

// si ya está aprobada, NO hacer nada
if ($registro["estado"] === "aprobada") {
    header("Location: revisar.php");
    exit;
}

// obtener valor de la tarea
$tarea = $supabase->from("tareas", "GET", null, "id=eq.$idTarea")[0];
$valorCoins = intval($tarea["coins_valor"]);

if ($accion === "aprobar") {

    // 1. Cambiar estado
    $supabase->from("tareas_realizadas", "PATCH", [
        "estado" => "aprobada",
        "coins_otorgados" => $valorCoins,
        "fecha_resolucion" => date("c")
    ], "id=eq.$id");

    // 2. Obtener coins actuales
    $perfil = $supabase->from("profiles", "GET", null, "id=eq.$idUsuario")[0];
    $coinsActuales = intval($perfil["coins"]);

    // 3. Sumar coins
    $supabase->from("profiles", "PATCH", [
        "coins" => $coinsActuales + $valorCoins
    ], "id=eq.$idUsuario");

    // 4. Registrar en historial
    $supabase->from("coins_movimientos", "POST", [
        "usuario_id" => $idUsuario,
        "tipo" => "tarea",
        "monto" => $valorCoins,
        "descripcion" => "Aprobación de tarea: {$tarea['titulo']}",
        "referencia" => $id,
        "fecha" => date("c")
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

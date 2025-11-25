<?php include "auth_check.php"; ?>

<?php require "../auth/check_admin.php"; require_once "../config/supabase.php";

$id = $_GET["id"];

$t = $supabase->from("tareas", "GET", null, "id=eq.$id")[0];
$newState = !$t["activa"];

$supabase->from("tareas", "PATCH", [
    "activa" => $newState
], "id=eq.$id");

header("Location: tareas.php");
exit;


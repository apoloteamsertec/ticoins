<?php include "header.php"; require "../config/supabase.php"; ?>

<?php
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Generar email interno (Supabase lo requiere)
    $email = $username . "@sistema.local";

    // 1. Crear usuario en AUTH
    $data = [
        "email" => $email,
        "password" => $password
    ];

    $url = $supabase->url . "/auth/v1/admin/users";
    $headers = [
        "apikey: {$supabase->service_key}",
        "Authorization: Bearer {$supabase->service_key}",
        "Content-Type: application/json"
    ];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $respuesta = json_decode(curl_exec($curl), true);

    if (isset($respuesta["id"])) {
        $uid = $respuesta["id"];

        // 2. Crear perfil
        $supabase->from("profiles", "POST", [
            "id" => $uid,
            "nombre_completo" => $nombre,
            "username" => $username,
            "rol" => "nino"
        ]);

        $mensaje = "Usuario creado correctamente.";
    } else {
        $mensaje = "Error creando usuario: " . json_encode($respuesta);
    }
}
?>

<h2>Crear nuevo usuario</h2>

<?php if($mensaje): ?>
<p><strong><?= $mensaje ?></strong></p>
<?php endif; ?>

<form method="POST" class="form-box">
    <label>Nombre completo</label>
    <input type="text" name="nombre" required>

    <label>Username</label>
    <input type="text" name="username" required>

    <label>Contraseña</label>
    <input type="password" name="password" required>

    <button type="submit">Crear usuario</button>
</form>


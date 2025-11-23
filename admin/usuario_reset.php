<?php include "header.php"; require_once "../config/supabase.php"; ?>

<?php
$id = $_GET["id"];
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPass = $_POST["password"];

    $url = $supabase->url . "/auth/v1/admin/users/$id";
    $headers = [
        "apikey: {$supabase->service_key}",
        "Authorization: Bearer {$supabase->service_key}",
        "Content-Type: application/json"
    ];

    $data = ["password" => $newPass];

    $c = curl_init($url);
    curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

    curl_exec($c);

    $mensaje = "Contraseña actualizada.";
}
?>

<h2>Resetear contraseña</h2>

<?php if($mensaje): ?><p><?= $mensaje ?></p><?php endif; ?>

<form method="POST" class="form-box">
    <label>Nueva contraseña</label>
    <input type="password" name="password" required>

    <button type="submit">Actualizar</button>
</form>
 </div><!-- .container -->
</div><!-- .page -->
</body>
</html>
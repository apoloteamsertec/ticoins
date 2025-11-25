<?php
session_start();
require_once '../config/supabase.php';


if (!isset($_SESSION["token"])) {
    header("Location: ../auth/login.php");
    exit;
}

// Obtener todos los usuarios tipo niño
$usuarios = $supabase->from(
    "profiles",
    "GET",
    null,
    "rol=eq.nino&select=id,nombre_completo,username,coins,foto_perfil"
);
?>
<?php include 'header.php'; ?>


<h1>Usuarios</h1>

<a href="usuario_nuevo.php" class="btn">➕ Nuevo usuario</a>
<br><br>

<table class="tabla">
    <tr>
        <th>Foto</th>
        <th>Nombre</th>
        <th>Usuario</th>
        <th>Coins</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($usuarios as $u): ?>
    <tr>
        <td>
            <?php if ($u["foto_perfil"]): ?>
                <img src="<?= $u["foto_perfil"] ?>" width="40" style="border-radius:50%;">
            <?php else: ?>
                <span>—</span>
            <?php endif; ?>
        </td>

        <td><?= $u["nombre_completo"] ?></td>
        <td><?= $u["username"] ?></td>
        <td><?= $u["coins"] ?></td>

        <td>
            <a href="usuario_editar.php?id=<?= $u["id"] ?>">Editar</a> |
            <a href="usuario_reset.php?id=<?= $u["id"] ?>">Reset Pass</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
 </div><!-- .container -->
</div><!-- .page -->
</body>
</html>
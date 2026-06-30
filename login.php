<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = ADMIN_USER;
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <main class="login-card">
        <h1>Panel administrativo</h1>
        <p>Ingresa para administrar el sorteo.</p>
        <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <label>Usuario</label>
            <input type="text" name="usuario" required>
            <label>Contraseña</label>
            <input type="password" name="password" required>
            <button class="btn primary full" type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>

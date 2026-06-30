<?php
require_once 'config.php';
require_admin();
$pdo = db();

$error = '';
$success = '';
$estadosPermitidos = ['vendido'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $numerosTexto = trim($_POST['numero_seleccionado'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');
    $estado = trim($_POST['estado'] ?? 'vendido');

    $numeros = preg_split('/\s*,\s*/', $numerosTexto, -1, PREG_SPLIT_NO_EMPTY);
    $numeros = array_values(array_unique(array_map(function ($n) {
        return str_pad(trim($n), 2, '0', STR_PAD_LEFT);
    }, $numeros)));

    if ($nombre === '' || $correo === '' || $telefono === '' || !$numeros) {
        $error = 'Completa nombre, correo, teléfono y al menos un número.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (!in_array($estado, $estadosPermitidos, true)) {
        $error = 'El estado seleccionado no es válido.';
    } else {
        foreach ($numeros as $numero) {
            if (!preg_match('/^[0-9]{2}$/', $numero)) {
                $error = 'Uno de los números no es válido. Usa formato 00 al 99.';
                break;
            }
        }
    }

    if ($error === '') {
        try {
            $pdo->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($numeros), '?'));
            $stmt = $pdo->prepare("SELECT numero, estado FROM numeros WHERE numero IN ($placeholders) FOR UPDATE");
            $stmt->execute($numeros);
            $rows = $stmt->fetchAll();

            if (count($rows) !== count($numeros)) {
                throw new RuntimeException('Uno de los números no existe.');
            }

            foreach ($rows as $row) {
                if ($row['estado'] !== 'disponible') {
                    throw new RuntimeException('El número ' . $row['numero'] . ' no está disponible.');
                }
            }

            $numerosFinal = implode(', ', $numeros);
            $stmt = $pdo->prepare('INSERT INTO participantes (nombre, correo, telefono, numero_seleccionado, comentario, estado) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nombre, $correo, $telefono, $numerosFinal, $comentario, $estado]);
            $participanteId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare("UPDATE numeros SET estado = ?, participante_id = ? WHERE numero IN ($placeholders)");
            $stmt->execute(array_merge([$estado, $participanteId], $numeros));

            $pdo->commit();
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$numerosDisponibles = $pdo->query("SELECT numero FROM numeros WHERE estado = 'disponible' ORDER BY numero ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar participante - <?= e(NOMBRE_SORTEO) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
<header class="admin-header">
    <div>
        <span class="dashboard-label">Dashboard</span>
        <h1>Agregar participante</h1>
    </div>
    <div class="admin-actions">
        <a href="dashboard.php" class="btn ghost">Volver al dashboard</a>
        <a href="logout.php" class="btn danger">Salir</a>
    </div>
</header>

<main class="admin-main">
    <?php if ($error): ?>
        <section class="notice error"><?= e($error) ?></section>
    <?php endif; ?>

    <section class="admin-panel manual-form-panel">
        <div class="panel-head">
            <h2>Registro manual</h2>
            <p>Usa este formulario para registrar personas.</p>
        </div>

        <form method="POST" class="manual-form">
            <div>
                <label>Nombre completo *</label>
                <input type="text" name="nombre" required maxlength="120">
            </div>
            <div>
                <label>Correo electrónico *</label>
                <input type="email" name="correo" required maxlength="150">
            </div>
            <div>
                <label>Número de teléfono *</label>
                <input type="tel" name="telefono" required maxlength="50">
            </div>
            <div>
                <label>Número o números seleccionados *</label>
                <input type="text" name="numero_seleccionado" required placeholder="Ejemplo: 05, 22, 48">
                <small>Separá varios números con coma.</small>
            </div>
            <div>
                <label>Estado *</label>
                <select name="estado" required>
                    <option value="vendido">Vendido</option>
                </select>
            </div>
            <div class="full-field">
                
            </div>
            <button type="submit" class="btn primary full-field">Guardar participante</button>
        </form>
    </section>

    <section class="admin-panel">
        <div class="panel-head">
            <h2>Números disponibles</h2>
            <p>Estos son los números que todavía puedes registrar manualmente.</p>
        </div>
        <div class="available-list">
            <?php foreach ($numerosDisponibles as $n): ?>
                <span><?= e($n['numero']) ?></span>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>

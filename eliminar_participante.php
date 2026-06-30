<?php
require_once 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$pdo = db();
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT numero_seleccionado FROM participantes WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if ($p) {
        $numeros = preg_split('/\s*,\s*/', $p['numero_seleccionado'], -1, PREG_SPLIT_NO_EMPTY);
        $numeros = array_values(array_unique($numeros));

        if ($numeros) {
            $placeholders = implode(',', array_fill(0, count($numeros), '?'));
            $stmt = $pdo->prepare("UPDATE numeros SET estado = 'disponible', participante_id = NULL WHERE numero IN ($placeholders)");
            $stmt->execute($numeros);
        }

        $stmt = $pdo->prepare('DELETE FROM participantes WHERE id = ?');
        $stmt->execute([$id]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
}

header('Location: dashboard.php');
exit;

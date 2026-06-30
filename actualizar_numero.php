<?php
require_once 'config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$numeroTexto = trim($_POST['numero'] ?? '');
$estado = trim($_POST['estado'] ?? '');
$participanteId = isset($_POST['participante_id']) ? (int)$_POST['participante_id'] : null;
$estadosPermitidos = ['disponible','reservado','vendido','bloqueado'];

$numeros = preg_split('/\s*,\s*/', $numeroTexto, -1, PREG_SPLIT_NO_EMPTY);
$numeros = array_values(array_unique(array_map(function ($n) {
    return str_pad(trim($n), 2, '0', STR_PAD_LEFT);
}, $numeros)));

if (!$numeros || !in_array($estado, $estadosPermitidos, true)) {
    header('Location: dashboard.php');
    exit;
}
foreach ($numeros as $numero) {
    if (!preg_match('/^[0-9]{2}$/', $numero)) {
        header('Location: dashboard.php');
        exit;
    }
}

$pdo = db();
$pdo->beginTransaction();
try {
    $placeholders = implode(',', array_fill(0, count($numeros), '?'));
    $stmt = $pdo->prepare("SELECT numero, participante_id FROM numeros WHERE numero IN ($placeholders) FOR UPDATE");
    $stmt->execute($numeros);
    $rows = $stmt->fetchAll();

    if (count($rows) !== count($numeros)) {
        throw new RuntimeException('Número no encontrado');
    }

    $currentParticipante = $participanteId;
    if (!$currentParticipante) {
        foreach ($rows as $row) {
            if (!empty($row['participante_id'])) {
                $currentParticipante = (int)$row['participante_id'];
                break;
            }
        }
    }

    $newParticipante = $estado === 'disponible' ? null : $currentParticipante;

    $stmt = $pdo->prepare("UPDATE numeros SET estado = ?, participante_id = ? WHERE numero IN ($placeholders)");
    $stmt->execute(array_merge([$estado, $newParticipante], $numeros));

    if ($currentParticipante) {
        $stmt = $pdo->prepare('UPDATE participantes SET estado = ? WHERE id = ?');
        $stmt->execute([$estado, $currentParticipante]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
}

header('Location: dashboard.php');
exit;

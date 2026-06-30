<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$numerosTexto = trim($_POST['numero_seleccionado'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');

if ($nombre === '' || $correo === '' || $telefono === '' || $numerosTexto === '') {
    header('Location: index.php?error=' . urlencode('Todos los campos obligatorios deben completarse.'));
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?error=' . urlencode('El correo electrónico no es válido.'));
    exit;
}

$numeros = preg_split('/\s*,\s*/', $numerosTexto, -1, PREG_SPLIT_NO_EMPTY);
$numeros = array_values(array_unique(array_map(function ($n) {
    return str_pad(trim($n), 2, '0', STR_PAD_LEFT);
}, $numeros)));

if (!$numeros) {
    header('Location: index.php?error=' . urlencode('Debes seleccionar al menos un número.'));
    exit;
}

foreach ($numeros as $numero) {
    if (!preg_match('/^[0-9]{2}$/', $numero)) {
        header('Location: index.php?error=' . urlencode('Uno de los números seleccionados no es válido.'));
        exit;
    }
}

$pdo = db();

try {
    $pdo->beginTransaction();

    $placeholders = implode(',', array_fill(0, count($numeros), '?'));
    $stmt = $pdo->prepare("SELECT numero, estado FROM numeros WHERE numero IN ($placeholders) FOR UPDATE");
    $stmt->execute($numeros);
    $rows = $stmt->fetchAll();

    if (count($rows) !== count($numeros)) {
        $pdo->rollBack();
        header('Location: index.php?error=' . urlencode('Uno de los números seleccionados no existe.'));
        exit;
    }

    foreach ($rows as $row) {
        if ($row['estado'] !== 'disponible') {
            $pdo->rollBack();
            header('Location: index.php?error=' . urlencode('El número ' . $row['numero'] . ' ya no está disponible. Selecciona otro.'));
            exit;
        }
    }

    $numerosFinal = implode(', ', $numeros);

    $stmt = $pdo->prepare('INSERT INTO participantes (nombre, correo, telefono, numero_seleccionado, comentario, estado) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$nombre, $correo, $telefono, $numerosFinal, $comentario, 'reservado']);
    $participanteId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("UPDATE numeros SET estado = ?, participante_id = ? WHERE numero IN ($placeholders)");
    $stmt->execute(array_merge(['reservado', $participanteId], $numeros));

    $pdo->commit();

    $mensaje = "Hola, quiero participar en el sorteo.\n\n" .
        "Nombre: {$nombre}\n" .
        "Correo: {$correo}\n" .
        "Teléfono: {$telefono}\n" .
        "Número seleccionado: {$numerosFinal}\n" .
        "Comentario: {$comentario}\n" .
        "Sorteo: " . NOMBRE_SORTEO;

    $url = 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . urlencode($mensaje);
    header('Location: ' . $url);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: index.php?error=' . urlencode('Ocurrió un error al registrar la participación.'));
    exit;
}

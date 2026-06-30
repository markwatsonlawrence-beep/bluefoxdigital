<?php
require_once 'config.php';
$pdo = db();
$numeros = $pdo->query("SELECT numero, estado FROM numeros ORDER BY numero ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(NOMBRE_SORTEO) ?></title>
    <meta name="description" content="Participa en nuestro sorteo seleccionando tu número favorito del 00 al 99.">
    <meta property="og:title" content="<?= e(NOMBRE_SORTEO) ?>">
    <meta property="og:description" content="Elige tu número, completa tus datos y confirma por WhatsApp.">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="hero">
    <nav class="nav">
        <div class="brand">🎁 <?= e(NOMBRE_SORTEO) ?></div>
        <a href="login.php" class="nav-link">Admin</a>
    </nav>

    <div class="hero-content hero-centered">
        <h1 class="hero-title-mega">¡PARTICIPA! <span class="hero-gold">¡5 NÚMEROS</span> SERÁN GANADORES!</h1>
    </div>
</header>

<main>
    <?php if (!empty($_GET['ok'])): ?>
        <section class="notice success">Tu solicitud fue registrada. Ahora confirma por WhatsApp.</section>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <section class="notice error"><?= e($_GET['error']) ?></section>
    <?php endif; ?>

    <section class="prize-block">
   
        <div class="prize-number prize-number--gold">200<span>km</span></div>
        <p class="prize-block-desc"> De traslados a donde desees, para quien acierte el número ganador.</p>
        <div class="prizes-footer">
            <span>🏅 <strong>5 números</strong> ganadores</span>
        </div>
    </section>

    <section class="vehicles-section">
  <div class="vehicles-container">

    <div class="vehicles-grid vehicles-grid--single vehicles-grid--wide">

      <div class="vehicle-gallery vehicle-gallery--3 vehicle-gallery--bare">
        <div class="photo-frame"><img src="s1.jpeg" alt="Foto exterior del vehículo 1"></div>
        <div class="photo-frame"><img src="s2.jpeg" alt="Foto trasera del vehículo 1"></div>
        <div class="photo-frame"><img src="s3.jpeg" alt="Foto trasera 2 del vehículo 1"></div>
      </div>

    </div>
  </div>
    </section>
    <section class="prize-block">
        <div class="prize-number prize-number--silver">10<span>km</span></div>
        <p class="prize-block-desc">De traslados para las 2 aproximaciones anteriores y posteriores al número ganador.</p>
        <div class="prizes-footer">
            <span>🏅 <strong>5 números</strong> ganadores</span>
        </div>
    </section>
    <section class="vehicles-section">
  <div class="vehicles-container">

    <div class="vehicles-grid vehicles-grid--single vehicles-grid--wide">

      <div class="vehicle-gallery vehicle-gallery--3 vehicle-gallery--bare">
        <div class="photo-frame"><img src="s4.jpeg" alt="Foto exterior del vehículo 2"></div>
        <div class="photo-frame"><img src="s5.jpeg" alt="Foto trasera del vehículo 2"></div>
      </div>

    </div>
  </div>
</section>

    <section class="instructions section">
        <h2>¿Cómo participar?</h2>
        <div class="steps">
            <article><strong>1</strong><span>Elige uno o varios números de la tabla.</span></article>
            <article><strong>2</strong><span>Completa tus datos (nombre, correo y teléfono).</span></article>
            <article><strong>3</strong><span>Envía tu solicitud — valor ₡2.000 por número.</span></article>
            <article><strong>4</strong><span>Recibí la confirmación del administrador.</span></article>
        </div>
    </section>
     


    <section id="participar" class="section layout">
        <div class="numbers-area">
            <div class="section-title">
                <span class="badge light">Tablero oficial</span>
                <h2>Números disponibles</h2>
                <p>Los números reservados no se pueden seleccionar. Puedes elegir más de un número disponible.</p>
            </div>

            <div class="legend">
                <span><i class="dot disponible"></i> Disponible</span>
                <span><i class="dot vendido"></i> Vendido</span>  
            </div>

            <div class="numbers-grid">
                <?php foreach ($numeros as $item): ?>
                    <?php $disabled = $item['estado'] !== 'disponible'; ?>
                    <button
                        type="button"
                        class="number-btn <?= e($item['estado']) ?>"
                        data-number="<?= e($item['numero']) ?>"
                        data-status="<?= e($item['estado']) ?>"
                        <?= $disabled ? 'disabled' : '' ?>
                    ><?= e($item['numero']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="form-card">
            <h2>Formulario de participación</h2>
            <form id="raffleForm" action="guardar_participante.php" method="POST">
                <label>Nombre completo *</label>
                <input type="text" name="nombre" id="nombre" required maxlength="120">

                <label>Correo electrónico *</label>
                <input type="email" name="correo" id="correo" required maxlength="150">

                <label>Número de teléfono *</label>
                <input type="tel" name="telefono" id="telefono" required maxlength="50">

                <label>Número seleccionado *</label>
                <input type="text" name="numero_seleccionado" id="numeroSeleccionado" readonly required placeholder="Selecciona uno o varios números">

                <label>Comentario opcional</label>
                <textarea name="comentario" id="comentario" rows="4" maxlength="500"></textarea>

                <button type="submit" class="btn primary full">Enviar por WhatsApp</button>
            </form>
        </aside>
    </section>

</main>

<footer class="footer">
    <p>© <?= date('Y') ?> <?= e(NOMBRE_SORTEO) ?>. Todos los derechos reservados. Diseñado por Blue Fox Digital</p>
</footer>

<script>
    const whatsappNumber = "<?= e(WHATSAPP_NUMBER) ?>";
    const raffleName = "<?= e(NOMBRE_SORTEO) ?>";
</script>
<script src="script.js"></script>
</body>
</html>

<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Glavni layout view aplikacije.
 * - Sadrži osnovnu HTML strukturu (head, body).
 * - Učitava Bootstrap CSS i JS iz lokalnih datoteka.
 * - Prikazuje navigacijsku traku s linkovima na početnu, odjavu i izbor jezika.
 * - Unutar <main> elementa se ubacuje sadržaj ($content) koji dolazi iz kontrolera.
 *
 * ===========================
 *  English
 * ===========================
 * Main layout view of the application.
 * - Contains the basic HTML structure (head, body).
 * - Loads Bootstrap CSS and JS from local files.
 * - Displays a navigation bar with links to home, logout, and language selection.
 * - Inside the <main> element, the controller-provided content ($content) is injected.
 */

use App\Core\App;
use App\Core\I18n;

?>
<!-- Local version -->
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::getLocale(), ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? htmlspecialchars($title) : _t('Aplikacija') ?></title>
  <base href="<?= App::baseHref(); ?>">
  <link href="<?= App::url('css/bootstrap.min.css') ?>?v=<?= filemtime(__DIR__ . '/../public/css/bootstrap.min.css') ?>"
        rel="stylesheet">
  <link
    href="<?= App::url('css/bootstrap-icons.css') ?>?v=<?= filemtime(__DIR__ . '/../public/css/bootstrap-icons.css') ?>"
    rel="stylesheet">
  <script
    src="<?= App::url('js/bootstrap.bundle.min.js') ?>?v=<?= filemtime(__DIR__ . '/../public/js/bootstrap.bundle.min.js') ?>"></script>
</head>

<!-- CDN Version -->
<!--
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php /*= isset($title) ? htmlspecialchars($title) : _t('Aplikacija') */?></title>
  <base href="<?php /*= App::baseHref(); */?>">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    rel="stylesheet">
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
</head>
-->


<body>
<?php include __DIR__ . '/menu.php'; ?>

<!-- Flash messages -->
<div class="container-fluid mt-3 px-3">
  <?php if ($msg = flash_get('success')): ?>
    <div class="alert alert-success alert-dismissible" role="alert">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if ($msg = flash_get('error')): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
</div>

<main class="container-fluid px-3">
  <?= $content ?? '' ?>
</main>
</body>
</html>

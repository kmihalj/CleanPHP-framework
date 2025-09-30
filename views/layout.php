<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Glavni layout view aplikacije.
 * - Definira osnovnu HTML strukturu (head, body).
 * - Učitava Bootstrap CSS i JS (lokalno ili preko CDN-a).
 * - Prikazuje glavnu navigaciju i flash poruke.
 * - U <main> element ubacuje sadržaj ($content) koji dolazi iz kontrolera.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Main layout view of the application.
 * - Defines the basic HTML structure (head, body).
 * - Loads Bootstrap CSS and JS (locally or via CDN).
 * - Displays the main navigation and flash messages.
 * - Inserts controller-provided content ($content) into the <main> element.
 */

// HR: Uvoz potrebnih klasa za aplikaciju i internacionalizaciju
// EN: Import required classes for the application and internationalization
use App\Core\App;
use App\Core\I18n;

?>
<!-- HR: Početak HTML dokumenta s definiranim jezikom prema odabranom locale-u -->
<!-- EN: Start of HTML document with language set according to selected locale -->
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::getLocale(), ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- HR: Naslov stranice - koristi varijablu $title ili zadanu vrijednost -->
  <!-- EN: Page title - uses $title variable or default value -->
  <title><?= isset($title) ? htmlspecialchars($title) : _t('Aplikacija') ?></title>
  <base href="<?= App::baseHref(); ?>">
  <!-- HR: Učitavanje Bootstrap CSS i ikona iz lokalnih datoteka -->
  <!-- EN: Loading Bootstrap CSS and icons from local files -->
  <link href="<?= App::url('css/bootstrap.min.css') ?>?v=<?= filemtime(__DIR__ . '/../public/css/bootstrap.min.css') ?>"
        rel="stylesheet">
  <link
    href="<?= App::url('css/bootstrap-icons.css') ?>?v=<?= filemtime(__DIR__ . '/../public/css/bootstrap-icons.css') ?>"
    rel="stylesheet">
  <script
    src="<?= App::url('js/bootstrap.bundle.min.js') ?>?v=<?= filemtime(__DIR__ . '/../public/js/bootstrap.bundle.min.js') ?>"></script>
</head>

<!-- HR: Alternativna verzija - učitavanje Bootstrap CSS/JS i ikona preko CDN-a -->
<!-- EN: Alternative version - loading Bootstrap CSS/JS and icons via CDN -->
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
<?php
// HR: Uključi datoteku s glavnim izbornikom
// EN: Include the main menu file
?>
<?php include __DIR__ . '/menu.php'; ?>

<!-- HR: Blok za prikaz flash poruka (uspjeh i greška) -->
<!-- EN: Block for displaying flash messages (success and error) -->
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

<!-- HR: Glavni sadržaj stranice - dinamički umetnut iz kontrolera -->
<!-- EN: Main page content - dynamically injected from the controller -->
<main class="container-fluid px-3">
  <?= $content ?? '' ?>
</main>
</body>
</html>

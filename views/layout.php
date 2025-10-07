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
<?php
// HR: Početak HTML dokumenta s definiranim jezikom prema odabranom locale-u
// EN: Start of HTML document with language set according to selected locale
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::getLocale(), ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  // HR: Naslov stranice - koristi varijablu $title ili zadanu vrijednost
  // EN: Page title - uses $title variable or default value
  ?>
  <title><?= isset($title) ? htmlspecialchars($title) : _t('Aplikacija') ?></title>
  <base href="<?= App::baseHref(); ?>">
  <?php
  // HR: Učitavanje Bootstrap CSS i ikona iz lokalnih datoteka
  // EN: Loading Bootstrap CSS and icons from local files
  ?>
  <link href="<?= App::url('css/bootstrap.min.css') ?>?v=<?= filemtime(__DIR__ . '/../public/css/bootstrap.min.css') ?>"
        rel="stylesheet">
  <link
    href="<?= App::url('css/bootstrap-icons.css') ?>?v=<?= filemtime(__DIR__ . '/../public/css/bootstrap-icons.css') ?>"
    rel="stylesheet">
  <script
    src="<?= App::url('js/bootstrap.bundle.min.js') ?>?v=<?= filemtime(__DIR__ . '/../public/js/bootstrap.bundle.min.js') ?>"></script>
</head>

<body>
<?php
// HR: Uključi datoteku s glavnim izbornikom
// EN: Include the main menu file
?>
<?php include __DIR__ . '/menu.php'; ?>

<?php
// HR: Blok za prikaz flash poruka (uspjeh i greška)
// EN: Block for displaying flash messages (success and error)
?>
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

<?php
// HR: Glavni sadržaj stranice - dinamički umetnut iz kontrolera
// EN: Main page content - dynamically injected from the controller
?>
<main class="container-fluid px-3">
  <?= $content ?? '' ?>
</main>
</body>
</html>

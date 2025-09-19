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
use App\Core\Csrf;

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::getLocale(), ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="UTF-8">
  <title><?= isset($title) ? htmlspecialchars($title) : _t('Aplikacija') ?></title>
  <base href="<?= App::baseHref(); ?>">
  <link href="<?= App::url('css/bootstrap.min.css') ?>?v=<?= filemtime(__DIR__ . '/../public/css/bootstrap.min.css') ?>"
        rel="stylesheet">
  <link
    href="<?= App::url('css/bootstrap-icons.css') ?>?v=<?= filemtime(__DIR__ . '/../public/css/bootstrap-icons.css') ?>"
    rel="stylesheet">
  <script
    src="<?= App::url('js/bootstrap.bundle.min.js') ?>?v=<?= filemtime(__DIR__ . '/../public/js/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= App::url('js/helpers.js') ?>?v=<?= filemtime(__DIR__ . '/../public/js/helpers.js') ?>"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= App::url(); ?>"><?= _t('CMS Auth Skeleton') ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <?php if (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'Admin'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown"
               aria-expanded="false">
              <?= _t('Admin') ?>
            </a>
            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
              <li><a class="dropdown-item" href="<?= App::urlFor('admin.users'); ?>"><?= _t('Popis korisnika') ?></a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown"
             aria-expanded="false">
            <?= _t('Jezik') ?>: <?= strtoupper(I18n::getLocale()) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
            <li><a class="dropdown-item" href="<?= App::url('lang/hr'); ?>"><?= _t('Hrvatski') ?></a></li>
            <li><a class="dropdown-item" href="<?= App::url('lang/en'); ?>"><?= _t('English') ?></a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= App::url('home'); ?>"><?= _t('Početna') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-warning" href="<?= App::url('logout'); ?>"><?= _t('Odjava') ?></a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<main class="container-fluid py-4">
  <?= $content ?? '' ?>
</main>
<div id="flash-messages" class="container mt-2"></div>
</body>
</html>

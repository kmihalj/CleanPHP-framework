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
use App\Core\Csrf;
use App\Core\I18n;

?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-2">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= App::url(); ?>"><?= _t('CMS Auth Skeleton') ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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
          <a class="nav-link" href="<?= App::urlFor('index'); ?>"><?= _t('Početna') ?></a>
        </li>
        <?php if (!isset($_SESSION['user'])): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
             aria-expanded="false">
            <?= _t('Korisnik') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="<?= App::urlFor('login.form'); ?>"><?= _t('Prijava') ?></a></li>
            <li><a class="dropdown-item" href="<?= App::urlFor('register.form'); ?>"><?= _t('Registracija') ?></a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
             aria-expanded="false">
            <?= htmlspecialchars($_SESSION['user']['ime'] . ' ' . $_SESSION['user']['prezime']) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="<?= App::urlFor('passwordChange.form'); ?>"><?= _t('Promjena lozinke') ?></a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="post" action="<?= App::urlFor('logout.submit'); ?>" style="margin: 0;">
                <?= Csrf::input() ?>
                <button type="submit" class="dropdown-item"><?= _t('Odjava') ?></button>
              </form>
            </li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

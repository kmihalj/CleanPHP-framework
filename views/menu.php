<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Pogled (view) menu.php definira glavnu navigacijsku traku aplikacije.
 * - Prikazuje link na početnu stranicu.
 * - Ako je korisnik administrator, dodaje Admin izbornik s opcijama.
 * - Omogućuje promjenu jezika.
 * - Ovisno o statusu prijave, prikazuje stavke za prijavu/registraciju
 *   ili ime korisnika s opcijama (promjena lozinke, odjava).
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The menu.php view defines the main navigation bar of the application.
 * - Displays a link to the home page.
 * - If the user is an administrator, shows an Admin menu with options.
 * - Provides language switching.
 * - Depending on login status, shows login/registration items
 *   or the user's name with options (password change, logout).
 */

use App\Core\App;
use App\Core\Csrf;
use App\Core\I18n;

?>

<!-- HR: Glavna navigacijska traka aplikacije / EN: Main application navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-2">
  <div class="container-fluid">
    <!-- HR: Link na početnu stranicu s imenom aplikacije / EN: Link to home page with app name -->
    <a class="navbar-brand" href="<?= App::url(); ?>"><?= _t('CMS Auth Skeleton') ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

        <!-- HR: Stavka izbornika - Početna stranica / EN: Menu item - Home page -->
        <li class="nav-item">
          <a class="nav-link" href="<?= App::urlFor('index'); ?>"><?= _t('Početna') ?></a>
        </li>

        <?php
        // HR: Ako je korisnik Admin, prikaži padajući izbornik s administratorskim opcijama
        // EN: If the user is Admin, display dropdown with administrative options
        if (isset($_SESSION['user']['role_name']) && $_SESSION['user']['role_name'] === 'Admin'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown"
               aria-expanded="false">
              <?= _t('Admin') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
              <!-- HR: Link na popis korisnika (samo za administratore) / EN: Link to user list (admin only) -->
              <li><a class="dropdown-item" href="<?= App::urlFor('admin.users'); ?>"><?= _t('Popis korisnika') ?></a></li>
            </ul>
          </li>
        <?php endif; ?>

        <!-- HR: Padajući izbornik za odabir jezika / EN: Dropdown menu for language selection -->
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

        <?php if (!isset($_SESSION['user'])): ?>
        <!-- HR: Padajući izbornik za goste (prijava i registracija) / EN: Dropdown menu for guests (login and register) -->
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
        <!-- HR: Padajući izbornik za prijavljenog korisnika s imenom i opcijama / EN: Dropdown menu for logged-in user with name and options -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
             aria-expanded="false">
            <?= htmlspecialchars($_SESSION['user']['ime'] . ' ' . $_SESSION['user']['prezime']) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <!-- HR: Link na promjenu lozinke / EN: Link to password change -->
            <li><a class="dropdown-item" href="<?= App::urlFor('passwordChange.form'); ?>"><?= _t('Promjena lozinke') ?></a></li>
            <li><hr class="dropdown-divider"></li> <!-- HR: Razdjelnik stavki izbornika / EN: Divider between menu items -->
            <!-- HR: Forma za odjavu korisnika s CSRF zaštitom / EN: User logout form with CSRF protection -->
            <li>
              <form method="post" action="<?= App::urlFor('logout.submit'); ?>">
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

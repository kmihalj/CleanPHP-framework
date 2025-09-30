<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Pogled (view) prijava.php prikazuje formu za prijavu korisnika.
 * Uključuje unos korisničkog imena i lozinke, CSRF zaštitu,
 * te link za resetiranje lozinke.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The prijava.php view displays the user login form.
 * Includes username and password input, CSRF protection,
 * and a link for password reset.
 */

use App\Core\Csrf;
use App\Core\App;

?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <?php
    // HR: Naslov stranice za prijavu / EN: Page title for login
    ?>
    <h1><?= _t('Prijava') ?></h1>
    <?php
    // HR: Forma za prijavu - koristi POST metodu i CSRF zaštitu / EN: Login form - uses POST method and CSRF protection
    ?>
    <form method="post" action="<?= App::urlFor('login.submit') ?>" class="mb-4">
      <?= Csrf::input(); ?>
      <div class="mb-3">
        <?php
        // HR: Polje za unos korisničkog imena / EN: Input field for username
        ?>
        <label for="korisnicko_ime" class="form-label"><?= _t('Korisničko ime') ?></label>
        <input
          type="text"
          name="korisnicko_ime"
          id="korisnicko_ime"
          class="form-control"
          placeholder="<?= _t('Korisničko ime') ?>"
          required
        >
      </div>
      <div class="mb-3">
        <?php
        // HR: Polje za unos lozinke / EN: Input field for password
        ?>
        <label for="lozinka" class="form-label"><?= _t('Lozinka') ?></label>
        <input
          type="password"
          name="lozinka"
          id="lozinka"
          class="form-control"
          placeholder="<?= _t('Lozinka') ?>"
          required
        >
      </div>
      <?php
      // HR: Gumbi za reset lozinke i potvrdu prijave / EN: Buttons for password reset and login submit
      ?>
      <div class="d-flex justify-content-between">
        <a href="<?= App::urlFor('passwordReset.form') ?>" class="btn btn-secondary">
          <?= _t('Zaboravio sam lozinku') ?>
        </a>
        <button type="submit" class="btn btn-primary"><?= _t('Prijava') ?></button>
      </div>
    </form>
  </div>
</div>

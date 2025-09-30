<?php

use App\Core\App;
use App\Core\Csrf;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Pogled (view) zaboravljenaLozinka.php prikazuje formu za resetiranje lozinke.
 * Korisnik unosi svoju e-mail adresu ili korisničko ime kako bi dobio upute
 * za resetiranje lozinke putem e-maila.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The zaboravljenaLozinka.php view displays a form for password reset.
 * The user enters their email address or username to receive
 * instructions for password reset via email.
 */

?>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <!-- HR: Naslov stranice - zaboravljena lozinka / EN: Page title - forgot password -->
      <h1><?= _t('Zaboravljena lozinka') ?></h1>
      <!-- HR: Forma za unos podataka potrebnih za reset lozinke / EN: Form for entering data required for password reset -->
      <form method="post" action="<?= App::urlFor('passwordReset.submit') ?>" autocomplete="off">
        <!-- HR: CSRF zaštita forme / EN: CSRF protection for the form -->
        <?= Csrf::input(); ?>
        <div class="mb-3">
          <!-- HR: Polje za unos e-mail adrese ili korisničkog imena / EN: Input field for entering email address or username -->
          <label for="korisnik" class="form-label"><?= _t('E-mail adresa ili korisničko ime') ?></label>
          <!-- HR: Input polje za korisnički identifikator (e-mail ili korisničko ime) / EN: Input field for user identifier (email or username) -->
          <input
            type="text"
            class="form-control <?= !empty($errors['korisnik']) ? 'is-invalid' : '' ?>"
            id="korisnik"
            name="korisnik"
            required
          >
        </div>
        <div class="d-flex justify-content-center">
          <!-- HR: Gumb za slanje zahtjeva za reset lozinke / EN: Button to submit password reset request -->
          <button type="submit" class="btn btn-primary">
            <?= _t('Reset lozinke') ?>
          </button>
        </div>
      </form>
    </div>
  </div>


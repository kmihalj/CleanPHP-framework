<?php

use App\Core\App;
use App\Core\Csrf;

/*
 * Zaboravljena lozinka / Forgot password
 *
 * Ova stranica sadrži formu za resetiranje lozinke korisnika.
 * Korisnik unosi svoju e-mail adresu kako bi dobio upute za reset lozinke.
 *
 * This page contains a form for user password reset.
 * The user enters their email address to receive instructions for password reset.
 */

?>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <h1><?= _t('Zaboravljena lozinka') ?></h1>
      <form method="post" action="<?= App::urlFor('passwordReset.submit') ?>" autocomplete="off">
        <!-- CSRF input / CSRF unos -->
        <?= Csrf::input(); ?>
        <div class="mb-3">
          <label for="korisnik" class="form-label"><?= _t('E-mail adresa ili korisničko ime') ?></label>
          <input
            type="text"
            class="form-control <?= !empty($errors['korisnik']) ? 'is-invalid' : '' ?>"
            id="korisnik"
            name="korisnik"
            required
          >
        </div>
        <div class="d-flex justify-content-center">
          <!-- Gumb za reset lozinke / Password reset button -->
          <button type="submit" class="btn btn-primary">
            <?= _t('Reset lozinke') ?>
          </button>
        </div>
      </form>
    </div>
  </div>


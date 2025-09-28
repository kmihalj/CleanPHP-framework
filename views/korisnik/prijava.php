<?php

use App\Core\Csrf;
use App\Core\App;

?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <h1><?= _t('Prijava') ?></h1>
    <form method="post" action="<?= App::urlFor('login.submit') ?>" class="mb-4">
      <?= Csrf::input(); ?>
      <div class="mb-3">
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
      <div class="d-flex justify-content-between">
        <a href="<?= App::urlFor('passwordReset.form') ?>" class="btn btn-secondary">
          <?= _t('Zaboravio sam lozinku') ?>
        </a>
        <button type="submit" class="btn btn-primary"><?= _t('Prijava') ?></button>
      </div>
    </form>
  </div>
</div>

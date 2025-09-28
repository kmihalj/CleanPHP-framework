<?php

use App\Core\App;
use App\Core\Csrf;

// Pripremi error i old podatke iz flash poruka
$errors = flash_get('errors');
$old = flash_get('old_input') ?? [];
?>
  <h2><?= _t('Registracija') ?></h2>
  <form method="post" action="<?= App::urlFor('register.submit') ?>">
    <?= Csrf::input() ?>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label for="ime" class="form-label"><?= _t('Ime') ?></label>
        <input type="text" class="form-control<?= isset($errors['ime']) ? ' is-invalid' : '' ?>" id="ime" name="ime"
               placeholder="<?= _t('Ime') ?>" value="<?= htmlspecialchars($old['ime'] ?? '') ?>" required>
        <?php if (isset($errors['ime'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['ime']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-md-4 mb-3">
        <label for="prezime" class="form-label"><?= _t('Prezime') ?></label>
        <input type="text" class="form-control<?= isset($errors['prezime']) ? ' is-invalid' : '' ?>" id="prezime"
               name="prezime" placeholder="<?= _t('Prezime') ?>" value="<?= htmlspecialchars($old['prezime'] ?? '') ?>" required>
        <?php if (isset($errors['prezime'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['prezime']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-md-4 mb-3">
        <label for="oib" class="form-label"><?= _t('OIB') ?></label>
        <input type="text" class="form-control<?= isset($errors['oib']) ? ' is-invalid' : '' ?>" id="oib" name="oib"
               maxlength="11" placeholder="<?= _t('OIB') ?>" value="<?= htmlspecialchars($old['oib'] ?? '') ?>" required>
        <?php if (isset($errors['oib'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['oib']) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="korisnicko_ime" class="form-label"><?= _t('Korisničko ime') ?></label>
        <input type="text" class="form-control<?= isset($errors['korisnicko_ime']) ? ' is-invalid' : '' ?>"
               id="korisnicko_ime" name="korisnicko_ime" placeholder="<?= _t('Korisničko ime') ?>"
               value="<?= htmlspecialchars($old['korisnicko_ime'] ?? '') ?>" required>
        <?php if (isset($errors['korisnicko_ime'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['korisnicko_ime']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-md-6 mb-3">
        <label for="email" class="form-label"><?= _t('E-mail') ?></label>
        <input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="email"
               name="email" placeholder="<?= _t('E-mail') ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
        <?php if (isset($errors['email'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['email']) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mb-3">
      <label for="lozinka" class="form-label"><?= _t('Lozinka') ?></label>
      <input type="password" class="form-control<?= isset($errors['lozinka']) ? ' is-invalid' : '' ?>" id="lozinka"
             name="lozinka" placeholder="<?= _t('Lozinka') ?>" required>
      <?php if (isset($errors['lozinka'])): ?>
        <div class="invalid-feedback">
          <?= htmlspecialchars($errors['lozinka']) ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="mb-3">
      <label for="potvrda_lozinke" class="form-label"><?= _t('Potvrda lozinke') ?></label>
      <input type="password" class="form-control<?= isset($errors['potvrda_lozinke']) ? ' is-invalid' : '' ?>"
             id="potvrda_lozinke" name="potvrda_lozinke" placeholder="<?= _t('Potvrda lozinke') ?>" required>
      <?php if (isset($errors['potvrda_lozinke'])): ?>
        <div class="invalid-feedback">
          <?= htmlspecialchars($errors['potvrda_lozinke']) ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-primary"><?= _t('Registriraj se') ?></button>
    </div>
  </form>

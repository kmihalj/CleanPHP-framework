<?php

use App\Core\App;
use App\Core\Csrf;
$errors = flash_get('errors');
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <h1><?= _t('Promjena lozinke') ?></h1>
    <form method="post" action="<?= App::urlFor('passwordChange.submit') ?>" autocomplete="off">
      <?= Csrf::input(); ?>
      <!-- Stara lozinka / Old password -->
      <div class="mb-3">
        <label for="lozinka" class="form-label"><?= _t('Stara lozinka') ?></label>
        <input type="password" class="form-control <?= !empty($errors['lozinka']) ? 'is-invalid' : '' ?>" id="lozinka"
               name="lozinka" required>
        <?php if (isset($errors['lozinka'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['lozinka']) ?>
          </div>
        <?php endif; ?>
      </div>
      <!-- Nova lozinka / New password -->
      <div class="mb-3">
        <label for="new_password" class="form-label"><?= _t('Nova lozinka') ?></label>
        <input type="password" class="form-control <?= !empty($errors['new_password']) ? 'is-invalid' : '' ?>"
               id="new_password" name="new_password" required>
        <?php if (!empty($errors['new_password'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['new_password']) ?>
          </div>
        <?php endif; ?>
      </div>
      <!-- Potvrda nove lozinke / Confirm new password -->
      <div class="mb-3">
        <label for="new_password_confirm" class="form-label"><?= _t('Potvrda nove lozinke') ?></label>
        <input type="password" class="form-control <?= !empty($errors['new_password_confirm']) ? 'is-invalid' : '' ?>"
               id="new_password_confirm" name="new_password_confirm" required>
        <?php if (!empty($errors['new_password_confirm'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['new_password_confirm']) ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="d-flex justify-content-center">
        <button type="submit" class="btn btn-primary"><?= _t('Promijeni lozinku') ?></button>
      </div>
    </form>
  </div>
</div>

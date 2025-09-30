<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Pogled (view) promjenaLozinke.php prikazuje formu za promjenu lozinke.
 * Uključuje unos stare lozinke, nove lozinke i potvrdu nove lozinke,
 * s validacijom i prikazom grešaka.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The promjenaLozinke.php view displays a form for changing the password.
 * Includes fields for old password, new password, and confirm new password,
 * with validation and error display.
 */

use App\Core\App;
use App\Core\Csrf;
// HR: Dohvaća eventualne greške spremljene u flash porukama
// EN: Retrieves any errors stored in flash messages
$errors = flash_get('errors');
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <!-- HR: Naslov stranice - promjena lozinke / EN: Page title - change password -->
    <h1><?= _t('Promjena lozinke') ?></h1>
    <!-- HR: Forma za promjenu lozinke, koristi POST metodu i CSRF zaštitu -->
    <!-- EN: Password change form, uses POST method and CSRF protection -->
    <form method="post" action="<?= App::urlFor('passwordChange.submit') ?>" autocomplete="off">
      <?= Csrf::input(); ?>
      <!-- HR: Polje za unos stare lozinke / EN: Input field for old password -->
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
      <!-- HR: Polje za unos nove lozinke / EN: Input field for new password -->
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
      <!-- HR: Polje za unos potvrde nove lozinke / EN: Input field for confirm new password -->
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
      <!-- HR: Gumb za potvrdu i slanje forme / EN: Button to confirm and submit form -->
      <div class="d-flex justify-content-center">
        <button type="submit" class="btn btn-primary"><?= _t('Promijeni lozinku') ?></button>
      </div>
    </form>
  </div>
</div>

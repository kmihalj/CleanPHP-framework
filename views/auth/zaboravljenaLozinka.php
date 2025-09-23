<?php

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
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <h1><?= _t('Zaboravljena lozinka') ?></h1>
      <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars(_t($_SESSION['flash_success'])) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
      <?php endif; ?>
      <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars(_t($_SESSION['flash_error'])) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
      <?php endif; ?>
      <?php if (!empty($errors['_general'])): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars(_t($errors['_general'])) ?>
        </div>
      <?php endif; ?>
      <form method="post" autocomplete="off">
        <!-- CSRF input / CSRF unos -->
        <?= Csrf::input(); ?>
        <!-- Email adresa / Email address -->
        <div class="mb-3">
          <label for="email" class="form-label"><?= _t('E-mail adresa') ?></label>
          <input
            type="email"
            class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
            id="email"
            name="email"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            required
          >
          <?php if (!empty($errors['email'])): ?>
            <div class="invalid-feedback">
              <?= htmlspecialchars($errors['email']) ?>
            </div>
          <?php endif; ?>
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
</div>

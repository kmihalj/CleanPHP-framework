<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * View datoteka za prijavu korisnika.
 * - Forma za prijavu s poljima korisničko ime i lozinka.
 * - Ako postoji greška, prikazuje se alert poruka.
 * - Forma uključuje CSRF zaštitu putem Csrf::input().
 *
 * ===========================
 *  English
 * ===========================
 * View file for user login.
 * - Login form with username and password fields.
 * - If an error exists, an alert message is displayed.
 * - Form includes CSRF protection using Csrf::input().
 */

use App\Core\App;
use App\Core\Csrf;

$errors = $errors ?? [];
$old = $old ?? [];
$error = $error ?? '';

?>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <h1><?= _t('Prijava') ?></h1>
      <?php if ($success = flash_get('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" action="<?= App::urlFor('login'); ?>" class="mb-4">
        <?= Csrf::input(); ?>
        <div class="mb-3">
          <label class="form-label" for="username"><?= _t('Korisničko ime') ?></label>
          <input
            type="text"
            name="username"
            id="username"
            class="form-control<?= !empty($errors['login_username'] ?? '') ? ' is-invalid' : '' ?>"
            value="<?= htmlspecialchars($old['login_username'] ?? '') ?>"
            required
          >
          <?php if (!empty($errors['login_username'] ?? '')): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['login_username']) ?></div>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <label class="form-label" for="password"><?= _t('Lozinka') ?></label>
          <input
            type="password"
            name="password"
            id="password"
            class="form-control<?= !empty($errors['login_password'] ?? '') ? ' is-invalid' : '' ?>"
            required
          >
          <?php if (!empty($errors['login_password'] ?? '')): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['login_password']) ?></div>
          <?php endif; ?>
        </div>
        <div class="d-flex justify-content-between">
          <a href="<?= App::url('forgot-password') ?>" class="btn btn-secondary">
            <?= _t('Zaboravio sam lozinku') ?>
          </a>
          <button type="submit" class="btn btn-primary"><?= _t('Prijava') ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * View datoteka samo za registraciju korisnika.
 *
 * ===========================
 *  English
 * ===========================
 * View file only for user registration.
 */

use App\Core\App;
use App\Core\Csrf;

$errors = $errors ?? [];
$old = $old ?? [];

?>
<div class="container">
<div class="row">
  <div class="col-md-12">
    <h2><?= _t('Registracija') ?></h2>
    <form method="post" action="<?= App::urlFor('register'); ?>">
      <?= Csrf::input(); ?>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="first_name"><?= _t('Ime') ?></label>
          <input
            type="text"
            name="first_name"
            id="first_name"
            class="form-control<?= !empty($errors['first_name'] ?? '') ? ' is-invalid' : '' ?>"
            value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
            required
          >
          <?php if (!empty($errors['first_name'] ?? '')): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
          <?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="last_name"><?= _t('Prezime') ?></label>
          <input
            type="text"
            name="last_name"
            id="last_name"
            class="form-control<?= !empty($errors['last_name'] ?? '') ? ' is-invalid' : '' ?>"
            value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
            required
          >
          <?php if (!empty($errors['last_name'] ?? '')): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="reg_username"><?= _t('Korisničko ime') ?></label>
        <input
          type="text"
          name="username"
          id="reg_username"
          class="form-control<?= !empty($errors['username'] ?? '') ? ' is-invalid' : '' ?>"
          value="<?= htmlspecialchars($old['username'] ?? '') ?>"
          required
        >
        <?php if (!empty($errors['username'] ?? '')): ?>
          <div class="invalid-feedback"><?= htmlspecialchars($errors['username']) ?></div>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label class="form-label" for="email"><?= _t('Email') ?></label>
        <input
          type="email"
          name="email"
          id="email"
          class="form-control<?= !empty($errors['email'] ?? '') ? ' is-invalid' : '' ?>"
          value="<?= htmlspecialchars($old['email'] ?? '') ?>"
          required
        >
        <?php if (!empty($errors['email'] ?? '')): ?>
          <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
        <?php endif; ?>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="reg_password"><?= _t('Lozinka') ?></label>
          <input
            type="password"
            name="password"
            id="reg_password"
            class="form-control<?= !empty($errors['password'] ?? '') ? ' is-invalid' : '' ?>"
            required
          >
          <?php if (!empty($errors['password'] ?? '')): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
          <?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="password_confirm"><?= _t('Ponovi lozinku') ?></label>
          <input
            type="password"
            name="password_confirm"
            id="password_confirm"
            class="form-control<?= !empty($errors['password_confirm'] ?? '') ? ' is-invalid' : '' ?>"
            required
          >
          <?php if (!empty($errors['password_confirm'] ?? '')): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['password_confirm']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <button type="submit" class="btn btn-success"><?= _t('Registracija') ?></button>
    </form>
  </div>
</div>
</div>

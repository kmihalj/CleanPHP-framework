<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * View datoteka za prijavu i registraciju korisnika.
 * - Lijeva strana: forma za prijavu s poljima korisničko ime i lozinka.
 * - Ako postoji greška, prikazuje se alert poruka.
 * - Desna strana: forma za registraciju s poljima ime, prezime, korisničko ime, email, lozinka i potvrda lozinke.
 * - Obje forme uključuju CSRF zaštitu putem Csrf::input().
 *
 * ===========================
 *  English
 * ===========================
 * View file for user login and registration.
 * - Left side: login form with username and password fields.
 * - If an error exists, an alert message is displayed.
 * - Right side: registration form with fields for first name, last name, username, email, password, and password confirmation.
 * - Both forms include CSRF protection using Csrf::input().
 */

use App\Core\App;
use App\Core\Csrf;

$errors = $errors ?? [];
$old = $old ?? [];
$error = $error ?? '';

?>
<div class="row">
  <div class="col-md-6">
    <h1><?= _t('Prijava') ?></h1>
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
      <button type="submit" class="btn btn-primary"><?= _t('Prijava') ?></button>
    </form>
  </div>
  <div class="col-md-6">
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

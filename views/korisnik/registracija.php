<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Pogled (view) registracija.php prikazuje formu za registraciju korisnika.
 * Uključuje unos osobnih podataka, korisničkog imena, e-maila, lozinke i potvrde lozinke.
 * Koristi CSRF zaštitu, validaciju i prikaz grešaka.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The registracija.php view displays the user registration form.
 * Includes input for personal data, username, email, password and password confirmation.
 * Uses CSRF protection, validation, and error display.
 */

use App\Core\App;
use App\Core\Csrf;

// HR: Dohvati eventualne greške i prethodno unesene vrijednosti iz flash poruka
// EN: Retrieve possible errors and previously entered values from flash messages
$errors = flash_get('errors');
$old = flash_get('old_input') ?? [];
?>
<!-- HR: Naslov stranice - registracija korisnika / EN: Page title - user registration -->
  <h2><?= _t('Registracija') ?></h2>
  <!-- HR: Forma za registraciju, koristi POST metodu i CSRF zaštitu / EN: Registration form, uses POST method and CSRF protection -->
  <form method="post" action="<?= App::urlFor('register.submit') ?>">
    <!-- HR: CSRF token za zaštitu forme / EN: CSRF token for form protection -->
    <?= Csrf::input() ?>
    <div class="row">
      <div class="col-md-4 mb-3">
        <!-- HR: Polje za unos imena / EN: Input field for first name -->
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
        <!-- HR: Polje za unos prezimena / EN: Input field for last name -->
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
        <!-- HR: Polje za unos OIB-a (11 znamenki) / EN: Input field for OIB (11 digits) -->
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
        <!-- HR: Polje za unos jedinstvenog korisničkog imena / EN: Input field for unique username -->
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
        <!-- HR: Polje za unos jedinstvene e-mail adrese / EN: Input field for unique email address -->
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
      <!-- HR: Polje za unos lozinke / EN: Input field for password -->
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
      <!-- HR: Polje za unos potvrde lozinke / EN: Input field for password confirmation -->
      <label for="potvrda_lozinke" class="form-label"><?= _t('Potvrda lozinke') ?></label>
      <input type="password" class="form-control<?= isset($errors['potvrda_lozinke']) ? ' is-invalid' : '' ?>"
             id="potvrda_lozinke" name="potvrda_lozinke" placeholder="<?= _t('Potvrda lozinke') ?>" required>
      <?php if (isset($errors['potvrda_lozinke'])): ?>
        <div class="invalid-feedback">
          <?= htmlspecialchars($errors['potvrda_lozinke']) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- HR: Gumb za slanje forme i registraciju / EN: Submit button for form registration -->
    <div class="text-center">
      <button type="submit" class="btn btn-primary"><?= _t('Registriraj se') ?></button>
    </div>
  </form>

<?php

use App\Core\Csrf;

?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1><?= _t('Promjena lozinke') ?></h1>
          <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors['_general'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars(_t($errors['_general'])) ?>
                </div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <?= Csrf::input(); ?>
                <!-- Stara lozinka / Old password -->
                <div class="mb-3">
                    <label for="old_password" class="form-label"><?= _t('Stara lozinka') ?></label>
                    <input type="password" class="form-control <?= !empty($errors['old_password']) ? 'is-invalid' : '' ?>" id="old_password" name="old_password" required>
                    <?php if (!empty($errors['old_password'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['old_password']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Nova lozinka / New password -->
                <div class="mb-3">
                    <label for="new_password" class="form-label"><?= _t('Nova lozinka') ?></label>
                    <input type="password" class="form-control <?= !empty($errors['new_password']) ? 'is-invalid' : '' ?>" id="new_password" name="new_password" required>
                    <?php if (!empty($errors['new_password'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['new_password']) ?>
                        </div>
                    <?php elseif (!empty($errors['_general'])): ?>
                        <div class="invalid-feedback">
                            <?= htmlspecialchars($errors['_general']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Potvrda nove lozinke / Confirm new password -->
                <div class="mb-3">
                    <label for="new_password_confirm" class="form-label"><?= _t('Potvrda nove lozinke') ?></label>
                    <input type="password" class="form-control <?= !empty($errors['new_password_confirm']) ? 'is-invalid' : '' ?>" id="new_password_confirm" name="new_password_confirm" required>
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
</div>

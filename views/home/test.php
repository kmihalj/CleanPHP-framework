<?php

use App\Core\App;
use App\Core\Csrf;

?>
<div class="row mb-3">
  <div class="col">
    <?php
    // Pripremi error i old podatke iz flash poruka
    $errors = flash_get('errors');
    $old = flash_get('old_input') ?? [];
    ?>
    <form action="<?= App::urlFor('test.form') ?>" method="post" novalidate>
      <?= Csrf::input() ?>
      <div class="mb-3">
        <label for="test_input" class="visually-hidden">Test input</label>
        <input
          type="text"
          name="test_input"
          id="test_input"
          class="form-control<?= isset($errors['test_input']) ? ' is-invalid' : '' ?>"
          placeholder="Test input"
          value="<?= htmlspecialchars($old['test_input'] ?? '') ?>"
          required
        >
        <?php if (isset($errors['test_input'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['test_input']) ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <label for="test_input2" class="visually-hidden">Test input 2</label>
        <input
          type="text"
          name="test_input2"
          id="test_input2"
          class="form-control<?= isset($errors['test_input2']) ? ' is-invalid' : '' ?>"
          placeholder="Test input 2"
          value="<?= htmlspecialchars($old['test_input2'] ?? '') ?>"
          required
        >
        <?php if (isset($errors['test_input2'])): ?>
          <div class="invalid-feedback">
            <?= htmlspecialchars($errors['test_input2']) ?>
          </div>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary"><?= _t('Testiraj unos') ?></button>
    </form>
  </div>
</div>
<div class="row">
  <div class="col mb-2">
    <form action="<?= App::urlFor('test.message.self') ?>" method="post">
      <?= Csrf::input() ?>
      <button type="submit" class="btn btn-primary w-100"><?= _t('Message self') ?></button>
    </form>
  </div>
  <div class="col mb-2">
    <form action="<?= App::urlFor('test.message.self.get') ?>" method="get">
      <button type="submit" class="btn btn-success w-100"><?= _t('Message self (GET)') ?></button>
    </form>
  </div>
  <div class="col mb-2">
    <form action="<?= App::urlFor('test.error.self') ?>" method="post">
      <?= Csrf::input() ?>
      <button type="submit" class="btn btn-danger w-100"><?= _t('Error self') ?></button>
    </form>
  </div>
  <div class="col mb-2">
    <form action="<?= App::urlFor('test.error.self.get') ?>" method="get">
      <button class="btn btn-warning w-100"><?= _t('Error self (GET)') ?></button>
    </form>
  </div>
</div>

<?php
/** @var string $dir */

use App\Core\App;
use App\Core\Csrf;

// HR: Dohvati eventualne greške i prethodno unesene vrijednosti iz flash poruka
// EN: Retrieve possible errors and previously entered values from flash messages
$errors = flash_get('errors');
$old = flash_get('old_input') ?? [];
?>


  <h1><?= _t('Upravljanje rolama') ?></h1>

  <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
      <thead class="table-secondary">
      <tr>
        <th class="text-nowrap">
          <?= _t('Rola') ?>
          <?php
          // HR: Trenutni smjer sortiranja iz GET parametra (default: asc)
          // EN: Current sort direction from GET param (default: asc)
          $dir = strtolower($_GET['dir'] ?? 'asc');
          $dir = in_array($dir, ['asc', 'desc'], true) ? $dir : 'asc';

          // HR: Sljedeći smjer (toggle) i ikona za prikaz
          // EN: Next direction (toggle) and icon to render
          $nextDir = ($dir === 'asc') ? 'desc' : 'asc';
          $iconHtml = ($dir === 'asc')
            ? '<i class="bi bi-sort-alpha-down"></i>'           // ostaje po tvom zahtjevu
            : '<i class="bi bi-sort-alpha-down-alt"></i>';    // ostaje po tvom zahtjevu

          // HR: Link uvijek sortira po "name" i koristi samo ?dir=...
          // EN: Link always sorts by "name" and only uses ?dir=...
          $sortUrl = App::urlFor('admin.roles') . '?dir=' . urlencode($nextDir);
          ?>
          <a href="<?= $sortUrl ?>" class="text-decoration-none"><?= $iconHtml /* HR/EN: render ikone bez escape-a */ ?>
          </a>
        </th>
        <th class="text-center text-nowrap"><?= _t('Radnje') ?></th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td class="text-nowrap"><?= _t('Gost') ?></td> <?php
        // HR: Statička uloga Gost / EN: Static role Guest
        ?>
        <td class="text-center text-nowrap">
          <button type="button" class="btn btn-sm border-0 bg-transparent p-0 text-muted disabled">
            <i class="bi bi-trash3"></i>
          </button>
        </td>
      </tr>
      <?php if (!empty($roles) && is_iterable($roles)): ?>
        <?php foreach ($roles as $rola): ?>
          <tr>
            <td class="text-nowrap"><?= htmlspecialchars($rola->name) ?></td>
            <td class="text-center text-nowrap">
              <?php if ($rola->name === 'Admin' || $rola->name === 'Registriran'): ?>
                <button type="button" class="btn btn-sm border-0 bg-transparent p-0 text-muted disabled">
                  <i class="bi bi-trash3"></i>
                </button>
              <?php else: ?>
                <form method="post" action="<?= App::urlFor('admin.roles.delete') ?>" class="d-inline">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="uuid" value="<?= htmlspecialchars($rola->uuid) ?>">
                  <input type="hidden" name="dir" value="<?= htmlspecialchars($dir ?? 'asc') ?>">
                  <button type="submit" class="btn btn-sm border-0 bg-transparent p-0 text-danger" aria-label="<?= _t('Obriši rolu') ?>">
                    <i class="bi bi-trash3"></i>
                  </button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td class="text-muted text-nowrap"><em>Nema definiranih rola / No roles defined</em></td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php
  // HR: Forma za dodavanje nove uloge / EN: Form to add a new role
  ?>
  <form method="post" action="<?= App::urlFor('admin.roles.create') ?>">
    <?= Csrf::input(); ?>
    <div class="mb-3">
      <label for="roleName" class="form-label mb-0 me-2"><?= _t('Naziv role:') ?></label>
      <div class="input-group w-auto">
        <input type="text" id="roleName" name="roleName"
               class="form-control<?= isset($errors['roleName']) ? ' is-invalid' : '' ?>"
               placeholder="<?= _t('Naziv role') ?>" value="<?= htmlspecialchars($old['roleName'] ?? '') ?>"
               aria-label="<?= _t('Naziv role') ?>" aria-describedby="addRoleButton" required>
        <input type="hidden" name="dir" value="<?= htmlspecialchars($dir ?? 'asc') ?>">
        <button type="submit" class="btn btn-primary" id="addRoleButton"
                aria-label="<?= _t('Dodaj novu rolu') ?>"><?= _t('Dodaj') ?></button>
      </div>
      <?php if (isset($errors['roleName'])): ?>
        <div class="invalid-feedback d-block"><?= htmlspecialchars($errors['roleName']) ?></div>
      <?php endif; ?>
    </div>
  </form>



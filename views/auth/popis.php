<?php

use App\Core\App;
use App\Core\Csrf;

/**
 * @var string $title Naslov stranice / Page title
 * @var array $users Popis korisnika / List of users
 * @var string $sort Trenutno sortirano polje / Currently sorted column
 * @var string $dir Smjer sortiranja (ASC/DESC) / Sort direction (ASC/DESC)
 * @var int $page Trenutna stranica / Current page
 * @var int|string $perPage Broj zapisa po stranici ili 'all' / Records per page or 'all'
 * @var int $total Ukupan broj zapisa / Total number of records
 * @var array $perPageOptions Opcije za broj zapisa po stranici / Options for records per page
 */

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Ova view datoteka prikazuje popis korisnika sa sljedećim značajkama:
 * * - Tablica sa korisnicima
 * * - Sortiranje po stupcima
 * * - Paginacija
 * * - Inline uređivanje korisnika
 * * - Modal za resetiranje lozinke
 * * - Modal za brisanje korisnika
 * * - Prikaz flash poruka o uspjehu ili grešci
 *
 * ===========================
 *  English
 * ===========================
 * Ova view datoteka prikazuje popis korisnika sa sljedećim značajkama:
 * * - Tablica sa korisnicima
 * * - Sortiranje po stupcima
 * * - Paginacija
 * * - Inline uređivanje korisnika
 * * - Modal za resetiranje lozinke
 * * - Modal za brisanje korisnika
 * * - Prikaz flash poruka o uspjehu ili grešci
 */
if (($_SESSION['role'] ?? '') !== 'Admin') {
  // Authorization check (Admin only) / Provjera ovlasti (samo Admin)
  echo '<div class="container mt-4"><div class="alert alert-danger">'
    . _t('Niste ovlašteni za pristup ovoj stranici')
    . '</div></div>';
  return;
}
?>
<div class="container-fluid mt-4">
  <!-- Container and heading / Kontejner i naslov -->
  <h2><?= htmlspecialchars($title) ?></h2>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
      <tr class="text-nowrap">
        <?php
        // Table headers with sorting icons / Zaglavlja tablice sa ikonama za sortiranje
        $baseUrl = App::urlFor('admin.users');
        $headers = [
          'edit' => '', // Edit column
          'id' => _t('ID'),
          'first_name' => _t('Ime'),
          'last_name' => _t('Prezime'),
          'username' => _t('Korisničko ime'),
          'email' => _t('Email'),
          'role' => _t('Uloga'),
          'created_at' => _t('Kreiran'),
          'actions' => _t('Akcije'), // Actions column
        ];
        foreach ($headers as $col => $label):
          if (in_array($col, ['edit', 'actions'])) {
            echo "<th>" . htmlspecialchars($label) . "</th>";
            continue;
          }
          $newDir = ($sort === $col && $dir === 'ASC') ? 'desc' : 'asc';
          $query = http_build_query([
            'sort' => $col,
            'dir' => $newDir,
            'page' => $page,
            'per_page' => $perPage
          ]);
          $icon = '<i class="bi bi-arrow-down-up text-dark"></i>'; // default neutral icon in black
          if ($sort === $col) {
            $icon = ($dir === 'ASC')
              ? '<i class="bi bi-caret-up-fill text-danger"></i>'
              : '<i class="bi bi-caret-down-fill text-danger"></i>';
          }
          echo "<th>" . htmlspecialchars($label) . " <a href=\"" . htmlspecialchars($baseUrl) . "?" . htmlspecialchars($query) . "\">$icon</a></th>";
        endforeach;
        ?>
      </tr>
      </thead>
      <tbody>
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
          <!-- Table body with user rows and action icons / Tjelo tablice sa redovima korisnika i ikonama akcija -->
          <tr data-id="<?= htmlspecialchars($user['id']) ?>">
            <td>
              <span class="action-edit text-primary" role="button" title="<?= _t('Uredi') ?>">✏️</span>
            </td>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td data-field="first_name"><?= htmlspecialchars($user['first_name']) ?></td>
            <td data-field="last_name"><?= htmlspecialchars($user['last_name']) ?></td>
            <td data-field="username"><?= htmlspecialchars($user['username']) ?></td>
            <td data-field="email"><?= htmlspecialchars($user['email']) ?></td>
            <td data-field="role"><?= htmlspecialchars($user['role']) ?></td>
            <td class="text-nowrap"><?= htmlspecialchars($user['created_at'] ?? '') ?></td>
            <td class="text-nowrap">
              <span class="action-delete text-danger me-2" role="button" title="<?= _t('Obriši') ?>"
                    data-bs-toggle="modal" data-bs-target="#deleteUserModal">🗑️</span>
              <span class="action-reset text-warning" role="button" title="<?= _t('Reset lozinke') ?>"
                    data-bs-toggle="modal" data-bs-target="#resetPasswordModal">🔑</span>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="9"><?= _t('Nema korisnika za prikaz') ?></td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($perPage !== 'all'):
    // Pagination block / Blok paginacije
    $totalPages = (int)ceil($total / $perPage);
    ?>
    <nav>
      <ul class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++):
          $query = http_build_query([
            'sort' => $sort,
            'dir' => strtolower($dir),
            'page' => $p,
            'per_page' => $perPage
          ]);
          ?>
          <li class="page-item<?= $p === $page ? ' active' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($baseUrl) ?>?<?= htmlspecialchars($query) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>

  <form method="get" action="<?= htmlspecialchars($baseUrl) ?>" class="mt-2">
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
    <input type="hidden" name="dir" value="<?= htmlspecialchars(strtolower($dir)) ?>">
    <input type="hidden" name="page" value="1">
    <label for="per_page"><?= _t('Prikaži') ?>:</label>
    <select name="per_page" id="per_page" onchange="this.form.submit()">
      <?php foreach ($perPageOptions as $opt):
        $sel = ((string)$opt === (string)$perPage) ? 'selected' : '';
        echo "<option value=\"" . htmlspecialchars($opt) . "\" $sel>" . htmlspecialchars($opt) . "</option>";
      endforeach; ?>
    </select>
  </form>
  <script>
    // JavaScript messages and URL constants / JS poruke i URL konstante
    const csrfToken = "<?= Csrf::token() ?>";
    const messages = {
      saved: "<?= _t('Podaci su spremljeni') ?>",
      error: "<?= _t('Greška pri spremanju') ?>",
      network: "<?= _t('Greška mreže') ?>",
      resetSuccess: "<?= _t('Lozinka je resetirana i poslana korisniku.') ?>",
      resetError: "<?= _t('Greška pri resetiranju lozinke.') ?>",
      networkReset: "<?= _t('Greška mreže pri resetiranju lozinke.') ?>",
      deleteSuccess: "<?= _t('Korisnik je uspješno obrisan.') ?>",
    };
    window.translations = {
      reset_message: "<?= _t('Korisniku \"%s\" će biti generirana nova lozinka i poslana mailom na adresu \"%s\".') ?>",
      delete_message: "<?= _t('Korisnik \"%s %s\" će biti obrisan.') ?>"
    };
    const updateUrlBase = "<?= App::url('admin/users/update') ?>";
    const resetUrlBase = "<?= App::url('admin/users/reset') ?>";
    const deleteUrlBase = "<?= App::url('admin/users/delete') ?>";
  </script>
  <!-- Included script for popis.js / Uključeni skript za popis.js -->
  <script src="<?= App::url('js/popis.js') ?>?v=<?= filemtime(dirname(__DIR__, 2) . '/public/js/popis.js') ?>"></script>
  <!-- Reset Password Modal explanation / Modal za reset lozinke -->
  <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="resetPasswordLabel"><?= _t('Reset lozinke') ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= _t('Zatvori') ?>"></button>
        </div>
        <div class="modal-body">
          <p id="resetPasswordMessage">
            <?= _t('Korisniku će biti generirana nova lozinka i poslana mailom.') ?>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _t('Odustani') ?></button>
          <button type="button" class="btn btn-primary" id="confirmResetPassword"><?= _t('Generiraj') ?></button>
        </div>
      </div>
    </div>
  </div>
  <!-- Delete User Modal explanation / Modal za potvrdu brisanja korisnika -->
  <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteUserLabel"><?= _t('Potvrda brisanja') ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= _t('Zatvori') ?>"></button>
        </div>
        <div class="modal-body">
          <p id="deleteUserMessage"></p>
        </div>
        <div class="modal-footer">
          <form id="deleteUserForm" method="post" action="<?= App::url('admin/users/delete') ?>" class="d-inline">
            <?= Csrf::input(); ?>
            <input type="hidden" name="id" id="deleteUserId">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
            <input type="hidden" name="per_page" value="<?= htmlspecialchars($perPage) ?>">
            <button type="submit" class="btn btn-danger"><?= _t('Obriši') ?></button>
          </form>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _t('Odustani') ?></button>
        </div>
      </div>
    </div>
  </div>
  <!-- Flash messages at bottom / Flash poruke na dnu -->
  <?php if ($msg = flash_get('success')): ?>
    <div class="container mt-4">
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    </div>
  <?php endif; ?>
  <?php if ($msg = flash_get('error')): ?>
    <div class="container mt-4">
      <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
    </div>
  <?php endif; ?>
</div>

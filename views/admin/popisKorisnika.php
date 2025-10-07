<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Pogled (view) popisKorisnika.php prikazuje popis korisnika s mogućnošću sortiranja,
 * paginacije i izvođenja administrativnih radnji (uređivanje, brisanje, reset lozinke).
 * Uključuje i Bootstrap modale za potvrdu radnji.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The popisKorisnika.php view displays a list of users with sorting,
 * pagination, and administrative actions (edit, delete, reset password).
 * It also includes Bootstrap modals for action confirmation.
 */

/** @var array $korisnici */
/** @var int|string $perPage */
/** @var int $page */
/** @var array $perPageOptions */
/** @var string $sort */
/** @var string $dir */
/** @var Rola[] $roleOptions */

/** @var int $total */

use App\Controllers\HelperController;
use App\Core\App;
use App\Core\Csrf;
use App\Core\I18n;
use App\Models\Rola;

// HR: Dohvati eventualne greške i prethodno unesene vrijednosti iz flash poruka
// EN: Retrieve possible errors and previously entered values from flash messages
$errors = flash_get('errors');
$old = flash_get('old_input') ?? [];
// HR: Definiranje varijable za pohranu vrijednosti pretrage iz GET parametra
// EN: Define variable to store search value from GET parameter
$search = $_GET['search'] ?? '';

// HR: Naslov stranice - Popis korisnika
// EN: Page title - User list
?>
  <h1><?= _t('Popis korisnika') ?></h1>

  <?php
  // HR: Forma za odabir broja zapisa po stranici i primjenu filtera
  // EN: Form for selecting number of records per page and applying filters
  ?>
  <div class="d-flex align-items-center mb-3">
    <form method="get" class="d-flex align-items-center" action="<?= App::urlFor('admin.users') ?>">
      <label for="per_page" class="me-2 mb-0"><?= _t('Broj po stranici:') ?></label>
      <select id="per_page" name="per_page" class="form-select form-select-sm w-auto me-2">
        <?php foreach ($perPageOptions as $opt): ?>
          <option value="<?= $opt ?>" <?= ($perPage == $opt) ? 'selected' : '' ?>>
            <?= $opt === 'all' ? _t('Svi') : $opt ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="sort" value="<?= $sort ?>">
      <input type="hidden" name="dir" value="<?= $dir ?>">
      <input type="hidden" name="page" value="<?= $page ?>">
      <?php
      // HR: Hidden polje za search parametar u formi za broj po stranici
      // EN: Hidden input for search parameter in per_page form
      ?>
      <?php if ($search !== ''): ?>
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
      <?php endif; ?>
      <input type="hidden" name="per_page_changed" value="1">
      <button type="submit" class="btn btn-sm btn-primary ms-2"><?= _t('Prikaži') ?></button>
    </form>

    <?php
    // HR: Forma za pretragu korisnika (search input i gumbi)
    // EN: Form for user search (search input and buttons)
    ?>
    <form method="get" class="mb-0 ms-auto d-flex align-items-center" action="<?= App::urlFor('admin.users') ?>">
      <input type="hidden" name="per_page" value="<?= $perPage ?>">
      <input type="hidden" name="sort" value="<?= $sort ?>">
      <input type="hidden" name="dir" value="<?= $dir ?>">
      <input type="hidden" name="page" value="1">
      <?php if ($search !== ''): ?>
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
      <?php endif; ?>
      <div class="input-group input-group-sm">
        <?php
        // HR: Search input polje za unos pojma pretrage
        // EN: Search input field for entering search term
        ?>
        <label for="search" class="visually-hidden"><?= _t('Pretraga korisnika') ?></label>
        <input type="text" class="form-control" id="search" name="search"
               placeholder="<?= _t('Pretraži...') ?>"
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
               title="<?= _t('Pretraga po: ime, prezime, korisničko ime, role, email, OIB') ?>">
        <?php
        // HR: Gumb za pokretanje pretrage (ikona povećala)
        // EN: Button to start search (magnifier icon)
        ?>
        <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        <?php if ($search !== ''): ?>
          <?php
          // HR: Gumb za resetiranje pretrage (ikona X)
          // EN: Button to reset search (X icon)
          ?>
          <a href="<?= App::urlFor('admin.users') ?>?per_page=<?= urlencode($perPage) ?>&sort=<?= urlencode($sort) ?>&dir=<?= urlencode($dir) ?>&page=1" class="btn btn-outline-danger">
            <i class="bi bi-x-lg"></i>
          </a>
        <?php endif; ?>
        <?php
        // HR: Gumb za CSV export odmah pored search inputa - UKLONJENO prema uputi
        // EN: CSV export button next to search input - REMOVED as per instruction
        $exportUrl = App::urlFor('admin.users.export') . '?per_page=' . urlencode($perPage)
          . '&sort=' . urlencode($sort)
          . '&dir=' . urlencode($dir)
          . '&page=1';
        if ($search !== '') {
          $exportUrl .= '&search=' . urlencode($search);
        }
        ?>
      </div>
    </form>
  </div>

  <?php
  // HR: Tablica s korisnicima, omogućuje sortiranje po kolonama
  // EN: Table with users, allows sorting by columns
  ?>
  <div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
      <thead class="table-secondary">
      <tr>
        <th class="text-center text-nowrap">
          <a href="<?= $exportUrl ?>" title="<?= _t('Export u CSV') ?>" class="text-success"><i class="bi bi-table"></i></a>
        </th>
        <th class="text-nowrap">
          <?= _t('Ime') ?>
          <?php if ($sort === 'ime' && $dir === 'asc'): ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=ime&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down"></i></a>
          <?php elseif ($sort === 'ime' && $dir === 'desc'): ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=ime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down-alt"></i></a>
          <?php else: ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=ime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-arrow-down-up text-muted"></i></a>
          <?php endif; ?>
        </th>
        <th class="text-nowrap">
          <?= _t('Prezime') ?>
          <?php if ($sort === 'prezime' && $dir === 'asc'): ?>
            <a
              href="<?= App::urlFor('admin.users') ?>?sort=prezime&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down"></i></a>
          <?php elseif ($sort === 'prezime' && $dir === 'desc'): ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=prezime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down-alt"></i></a>
          <?php else: ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=prezime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-arrow-down-up text-muted"></i></a>
          <?php endif; ?>
        </th>
        <th class="text-nowrap">
          <?= _t('Korisničko ime') ?>
          <?php if ($sort === 'korisnicko_ime' && $dir === 'asc'): ?>
            <a
              href="<?= App::urlFor('admin.users') ?>?sort=korisnicko_ime&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down"></i></a>
          <?php elseif ($sort === 'korisnicko_ime' && $dir === 'desc'): ?>
            <a
              href="<?= App::urlFor('admin.users') ?>?sort=korisnicko_ime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down-alt"></i></a>
          <?php else: ?>
            <a
              href="<?= App::urlFor('admin.users') ?>?sort=korisnicko_ime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-arrow-down-up text-muted"></i></a>
          <?php endif; ?>
        </th>
        <th class="text-nowrap">
          <?= _t('Rola') ?>
          <?php if ($sort === 'role' && $dir === 'asc'): ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=role&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down"></i></a>
          <?php elseif ($sort === 'role' && $dir === 'desc'): ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=role&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down-alt"></i></a>
          <?php else: ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=role&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-arrow-down-up text-muted"></i></a>
          <?php endif; ?>
        </th>
        <th class="text-nowrap">
          <?= _t('Email') ?>
          <?php if ($sort === 'email' && $dir === 'asc'): ?>
            <a
              href="<?= App::urlFor('admin.users') ?>?sort=email&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down"></i></a>
          <?php elseif ($sort === 'email' && $dir === 'desc'): ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=email&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down-alt"></i></a>
          <?php else: ?>
            <a href="<?= App::urlFor('admin.users') ?>?sort=email&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-arrow-down-up text-muted"></i></a>
          <?php endif; ?>
        </th>
        <th class="text-nowrap"><?= _t('OIB') ?></th>
        <th class="text-nowrap">
          <?= _t('Kreiran') ?>
          <?php if ($sort === 'created_at' && $dir === 'asc'): ?>
            <a
              href="<?= App::urlFor('admin.users') ?>?sort=created_at&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down"></i></a>
          <?php elseif ($sort === 'created_at' && $dir === 'desc'): ?>
            <a
              href="<?= App::urlFor('admin.users') ?>?sort=created_at&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-sort-alpha-down-alt"></i></a>
          <?php else: ?>
            <a
              href="<?= App::urlFor('admin.users') ?>?sort=created_at&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"><i
                class="bi bi-arrow-down-up text-muted"></i></a>
          <?php endif; ?>
        </th>
        <th class="text-center text-nowrap"><?= _t('Radnje') ?></th>
      </tr>
      </thead>
      <tbody>
      <?php
      // HR: Postavke lokalizacije i formatiranje datuma
      // EN: Locale settings and date formatting
      $appConfig = require __DIR__ . '/../../config/app.php';
      $locales = $appConfig['locales'] ?? [];
      $locale = I18n::getLocale();
      $fmtLocale = $locales[$locale] ?? $locale;
      // HR: Provjera postoji li IntlDateFormatter za lokalizirano formatiranje datuma
      // EN: Check if IntlDateFormatter exists for localized date formatting
      if (class_exists('IntlDateFormatter')) {
        $formatter = new IntlDateFormatter($fmtLocale, IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
      } else {
        $formatter = null;
      }
      $currentUserUuid = $_SESSION['user']['uuid'] ?? null;
      ?>
      <?php
      // HR: Iteracija kroz sve korisnike i prikaz pojedinačnog retka tablice
      // EN: Iterate through all users and display each table row
      ?>
      <?php foreach ($korisnici as $korisnik): ?>
        <?php
        // HR: Provjera da li je korisnik trenutno logirani (onemogućava brisanje/reset lozinke samog sebe)
        // EN: Check if the user is the currently logged-in one (disables self delete/reset password)
        $isSelf = $korisnik['uuid'] === $currentUserUuid;
        ?>
        <tr>
          <td class="text-center text-nowrap">
            <?php
            // HR: Dugme za uređivanje sada koristi više rola; role i role_uuid polja su polja, pa ih kodiramo kao JSON za frontend
            // EN: Edit button now supports multiple roles; role and role_uuid fields are arrays, so we encode them as JSON for the frontend
            ?>
            <button type="button"
                    class="btn btn-sm border-0 bg-transparent p-0 text-secondary edit-user-btn"
                    data-uuid="<?= $korisnik['uuid'] ?>"
                    data-ime="<?= htmlspecialchars($korisnik['ime']) ?>"
                    data-prezime="<?= htmlspecialchars($korisnik['prezime']) ?>"
                    data-username="<?= htmlspecialchars($korisnik['korisnicko_ime']) ?>"
                    data-email="<?= htmlspecialchars($korisnik['email']) ?>"
                    data-oib="<?= htmlspecialchars($korisnik['oib']) ?>"
                    data-role-uuid='<?= json_encode($korisnik['role_uuid'] ?? []) ?>'
                    data-bs-toggle="modal"
                    data-bs-target="#editModal">
              <i class="bi bi-pencil-square"></i>
            </button>
          </td>
          <td class="text-nowrap"><?= htmlspecialchars($korisnik['ime']) ?></td>
          <td class="text-nowrap"><?= htmlspecialchars($korisnik['prezime']) ?></td>
          <td class="text-nowrap"><?= htmlspecialchars($korisnik['korisnicko_ime']) ?></td>
         <td class="text-nowrap">
            <?php
            // HR: Prikaz svih rola korisnika (string spojen u SQL-u pomoću GROUP_CONCAT)
            // EN: Display all user roles (string joined in SQL with GROUP_CONCAT)
            echo htmlspecialchars($korisnik['roles'] ?? '');
            ?>
          </td>
          <td class="text-nowrap"><?= htmlspecialchars($korisnik['email']) ?></td>
          <td class="text-nowrap"><?= htmlspecialchars($korisnik['oib']) ?></td>
          <td class="text-nowrap">
            <?php
            $timestamp = strtotime($korisnik['created_at']);
            // HR: Ako IntlDateFormatter nije dostupan, koristi standardni PHP date() kao fallback
            // EN: If IntlDateFormatter is not available, use standard PHP date() as fallback
            if ($formatter) {
              echo $formatter->format($timestamp);
            } else {
              echo date("d.m.Y H:i", $timestamp);
            }
            ?>
          </td>
          <td class="text-center text-nowrap">
            <?php
            // HR: Gumbi za administrativne radnje (uredi, obriši, reset lozinke)
            // EN: Buttons for administrative actions (edit, delete, reset password)
            ?>
            <button type="button"
                    class="btn btn-sm border-0 bg-transparent p-0 <?= $isSelf ? 'text-muted' : 'text-danger' ?>"
                    data-uuid="<?= $korisnik['uuid'] ?>"
                    data-name="<?= htmlspecialchars($korisnik['ime'] . ' ' . $korisnik['prezime']) ?>"
                    data-action="delete"
              <?= $isSelf ? 'disabled' : '' ?>
                    data-bs-toggle="modal" data-bs-target="#deleteModal">
              <i class="bi bi-trash3"></i>
            </button>
            &nbsp;
            <button type="button"
                    class="btn btn-sm border-0 bg-transparent p-0 <?= $isSelf ? 'text-muted' : 'text-warning' ?>"
                    data-uuid="<?= $korisnik['uuid'] ?>"
                    data-name="<?= htmlspecialchars($korisnik['ime'] . ' ' . $korisnik['prezime']) ?>"
                    data-action="reset"
              <?= $isSelf ? 'disabled' : '' ?>
                    data-bs-toggle="modal" data-bs-target="#resetModal">
              <i class="bi bi-key"></i>
            </button>
          </td>
        </tr>

      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php
  // HR: Paginacija na dnu tablice
  // EN: Pagination at the bottom of the table
  ?>
  <?php
  // HR: Prosljeđivanje search parametra u paginaciju
  // EN: Passing search parameter to pagination
  ?>
  <?= new HelperController()->renderPagination($page, $perPage, $total, $sort, $dir, App::urlFor('admin.users'), $search ?? null) ?>

  <?php
  // HR: Modal za potvrdu brisanja korisnika
  // EN: Modal for confirming user deletion
  ?>
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><?= _t('Potvrda brisanja') ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p><?= _t('Sigurno želite obrisati korisnika') ?> <span id="deleteUserName"></span>?</p>
        </div>
        <div class="modal-footer">
          <form method="post" id="deleteForm" action="<?= App::urlFor('admin.users.delete') ?>">
            <?= Csrf::input(); ?>
            <input type="hidden" name="uuid" id="deleteUserUuid">
            <input type="hidden" name="per_page" value="<?= htmlspecialchars($perPage) ?>">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
            <?php
            // HR: Hidden input za search parametar u formi za brisanje
            // EN: Hidden input for search parameter in delete form
            ?>
            <?php if ($search !== ''): ?>
              <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-danger"><?= _t('Obriši') ?></button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _t('Odustani') ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>

<script src="<?= App::url('js/modal.js') ?>?v=<?= filemtime(__DIR__ . '/../../public/js/modal.js') ?>"></script>
<script
  src="<?= App::url('js/popisKorisnika.php.js') ?>?v=<?= filemtime(__DIR__ . '/../../public/js/popisKorisnika.php.js') ?>"></script>

<?php
// HR: Modal za potvrdu resetiranja lozinke korisnika
// EN: Modal for confirming user password reset
?>
<div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= _t('Potvrda resetiranja lozinke') ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><?= _t('Sigurno želite resetirati lozinku korisnika') ?> <span id="resetUserName"></span>?</p>
      </div>
      <div class="modal-footer">
        <form method="post" id="resetForm" action="<?= App::urlFor('admin.users.resetPassword') ?>">
          <?= Csrf::input(); ?>
          <input type="hidden" name="uuid" id="resetUserUuid">
          <input type="hidden" name="per_page" value="<?= htmlspecialchars($perPage) ?>">
          <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
          <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
          <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
          <?php
          // HR: Hidden input za search parametar u formi za reset lozinke
          // EN: Hidden input for search parameter in reset password form
          ?>
          <?php if ($search !== ''): ?>
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
          <?php endif; ?>
          <button type="submit" class="btn btn-warning"><?= _t('Resetiraj lozinku') ?></button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _t('Odustani') ?></button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
// HR: Modal za uređivanje korisnika. Specifično uključuje podršku za prikaz grešaka validacije i automatsko ponovno otvaranje ako greške postoje.
// EN: Modal for editing user details. Specifically includes support for displaying validation errors and automatically reopening if errors are present.
?>
<div id="edit-verify" <?= (!empty($errors['editIme']) || !empty($errors['editPrezime']) || !empty($errors['editUsername']) || !empty($errors['editEmail']) || !empty($errors['editOib']) || !empty($errors['editRole'])) ? 'data-has-edit-errors="1"' : '' ?>></div>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= _t('Uredi korisnika') ?></h5>
        <a href="<?= App::urlFor('admin.users') ?>?per_page=<?= urlencode($perPage) ?>&sort=<?= urlencode($sort) ?>&dir=<?= urlencode($dir) ?>&page=<?= urlencode($page) ?>"
           class="btn-close" aria-label="<?= _t('Zatvori') ?>"></a>
      </div>
      <div class="modal-body">
        <form method="post" id="editForm" action="<?= App::urlFor('admin.users.edit') ?>">
          <?= Csrf::input(); ?>
          <input type="hidden" name="uuid" id="editUserUuid" value="<?= htmlspecialchars($old['uuid'] ?? '') ?>">
          <input type="hidden" name="per_page" value="<?= htmlspecialchars($perPage) ?>">
          <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
          <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
          <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
          <?php
          // HR: Hidden input za search parametar u formi za uređivanje korisnika
          // EN: Hidden input for search parameter in edit user form
          ?>
          <?php if ($search !== ''): ?>
          <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
          <?php endif; ?>
          <!-- HR: Prvi red - Ime, Prezime, Korisničko ime -->
          <!-- EN: First row - First name, Last name, Username -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="editIme" class="form-label"><?= _t('Ime') ?></label>
              <input type="text"
                     class="form-control<?= isset($errors['editIme']) ? ' is-invalid' : '' ?>"
                     id="editIme" name="editIme"
                     value="<?= htmlspecialchars($old['editIme'] ?? '') ?>" required>
              <?php if (isset($errors['editIme'])): ?>
                <div class="invalid-feedback">
                  <?= htmlspecialchars($errors['editIme']) ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
              <label for="editPrezime" class="form-label"><?= _t('Prezime') ?></label>
              <input type="text"
                     class="form-control<?= isset($errors['editPrezime']) ? ' is-invalid' : '' ?>"
                     id="editPrezime" name="editPrezime"
                     value="<?= htmlspecialchars($old['editPrezime'] ?? '') ?>" required>
              <?php if (isset($errors['editPrezime'])): ?>
                <div class="invalid-feedback">
                  <?= htmlspecialchars($errors['editPrezime']) ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
              <label for="editUsername" class="form-label"><?= _t('Korisničko ime') ?></label>
              <input type="text"
                     class="form-control<?= isset($errors['editUsername']) ? ' is-invalid' : '' ?>"
                     id="editUsername" name="editUsername"
                     value="<?= htmlspecialchars($old['editUsername'] ?? '') ?>" required>
              <?php if (isset($errors['editUsername'])): ?>
                <div class="invalid-feedback">
                  <?= htmlspecialchars($errors['editUsername']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- HR: Drugi red - E-mail, OIB -->
          <!-- EN: Second row - Email, OIB -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editEmail" class="form-label"><?= _t('E-mail') ?></label>
              <input type="email"
                     class="form-control<?= isset($errors['editEmail']) ? ' is-invalid' : '' ?>"
                     id="editEmail" name="editEmail"
                     value="<?= htmlspecialchars($old['editEmail'] ?? '') ?>" required>
              <?php if (isset($errors['editEmail'])): ?>
                <div class="invalid-feedback">
                  <?= htmlspecialchars($errors['editEmail']) ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
              <label for="editOib" class="form-label"><?= _t('OIB') ?></label>
              <input type="text"
                     class="form-control<?= isset($errors['editOib']) ? ' is-invalid' : '' ?>"
                     id="editOib" name="editOib" maxlength="11"
                     value="<?= htmlspecialchars($old['editOib'] ?? '') ?>" required>
              <?php if (isset($errors['editOib'])): ?>
                <div class="invalid-feedback">
                  <?= htmlspecialchars($errors['editOib']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- HR: Treći red - Rola (checkboxovi u liniji) -->
          <!-- EN: Third row - Roles (checkboxes inline) -->
          <div class="row">
            <div class="col-12 mb-3">
              <label class="form-label"><?= _t('Rola') ?></label>
              <?php
              // HR: Bootstrap 5 switch checkboxes za odabir više rola, prikazani u liniji
              // EN: Bootstrap 5 switch checkboxes for selecting multiple roles, displayed inline
              ?>
              <div>
                <?php foreach (($roleOptions ?? []) as $rola): ?>
                  <?php
                  $checked = false;
                  if (isset($old['editRole'])) {
                      $checked = is_array($old['editRole'])
                          ? in_array($rola->uuid, $old['editRole'])
                          : $old['editRole'] == $rola->uuid;
                  } elseif (!empty($korisnik['role_uuid'])) {
                      $userRoles = is_array($korisnik['role_uuid'])
                          ? $korisnik['role_uuid']
                          : json_decode($korisnik['role_uuid'], true);
                      if (is_array($userRoles)) {
                          $checked = in_array($rola->uuid, $userRoles);
                      }
                  }
                  ?>
                  <div class="form-check form-switch form-check-inline mb-1">
                    <input
                      class="form-check-input<?= isset($errors['editRole']) ? ' is-invalid' : '' ?>"
                      type="checkbox"
                      role="switch"
                      id="editRole_<?= $rola->uuid ?>"
                      name="editRole[]"
                      value="<?= $rola->uuid ?>"
                      <?php if ($checked): ?>checked<?php endif; ?>
                    >
                    <label class="form-check-label" for="editRole_<?= $rola->uuid ?>">
                      <?= htmlspecialchars($rola->name) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php if (isset($errors['editRole'])): ?>
                <div class="invalid-feedback d-block">
                  <?= htmlspecialchars($errors['editRole']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="submit" form="editForm" class="btn btn-primary"><?= _t('Spremi') ?></button>
        <a href="<?= App::urlFor('admin.users') ?>?per_page=<?= urlencode($perPage) ?>&sort=<?= urlencode($sort) ?>&dir=<?= urlencode($dir) ?>&page=<?= urlencode($page) ?>"
           class="btn btn-secondary"><?= _t('Odustani') ?></a>
      </div>
    </div>
  </div>
</div>

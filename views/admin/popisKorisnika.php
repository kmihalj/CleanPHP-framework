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
/** @var int $total */

use App\Controllers\HelperController;
use App\Core\App;
use App\Core\Csrf;
use App\Core\I18n;

// HR: Naslov stranice - Popis korisnika
// EN: Page title - User list
?>
  <h1><?= _t('Popis korisnika') ?></h1>

  <?php
  // HR: Forma za odabir broja zapisa po stranici i primjenu filtera
  // EN: Form for selecting number of records per page and applying filters
  ?>
  <form method="get" class="mb-3 d-flex align-items-center" action="<?= App::urlFor('admin.users') ?>">
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
    <button type="submit" class="btn btn-sm btn-primary ms-2"><?= _t('Prikaži') ?></button>
  </form>

  <?php
  // HR: Tablica s korisnicima, omogućuje sortiranje po kolonama
  // EN: Table with users, allows sorting by columns
  ?>
  <div class="table-responsive">
  <table class="table table-striped table-bordered table-hover">
    <thead class="table-secondary">
    <tr>
      <th class="text-center text-nowrap"></th>
      <th class="text-nowrap">
        <?= _t('Ime') ?>
        <?php if ($sort === 'ime' && $dir === 'asc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=ime&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down"></i></a>
        <?php elseif ($sort === 'ime' && $dir === 'desc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=ime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down-alt"></i></a>
        <?php else: ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=ime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-arrow-down-up text-muted"></i></a>
        <?php endif; ?>
      </th>
      <th class="text-nowrap">
        <?= _t('Prezime') ?>
        <?php if ($sort === 'prezime' && $dir === 'asc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=prezime&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down"></i></a>
        <?php elseif ($sort === 'prezime' && $dir === 'desc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=prezime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down-alt"></i></a>
        <?php else: ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=prezime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-arrow-down-up text-muted"></i></a>
        <?php endif; ?>
      </th>
      <th class="text-nowrap">
        <?= _t('Korisničko ime') ?>
        <?php if ($sort === 'korisnicko_ime' && $dir === 'asc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=korisnicko_ime&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down"></i></a>
        <?php elseif ($sort === 'korisnicko_ime' && $dir === 'desc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=korisnicko_ime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down-alt"></i></a>
        <?php else: ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=korisnicko_ime&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-arrow-down-up text-muted"></i></a>
        <?php endif; ?>
      </th>
      <th class="text-nowrap">
        <?= _t('Rola') ?>
        <?php if ($sort === 'role' && $dir === 'asc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=role&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down"></i></a>
        <?php elseif ($sort === 'role' && $dir === 'desc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=role&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down-alt"></i></a>
        <?php else: ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=role&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-arrow-down-up text-muted"></i></a>
        <?php endif; ?>
      </th>
      <th class="text-nowrap">
        <?= _t('Email') ?>
        <?php if ($sort === 'email' && $dir === 'asc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=email&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down"></i></a>
        <?php elseif ($sort === 'email' && $dir === 'desc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=email&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down-alt"></i></a>
        <?php else: ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=email&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-arrow-down-up text-muted"></i></a>
        <?php endif; ?>
      </th>
      <th class="text-nowrap"><?= _t('OIB') ?></th>
      <th class="text-nowrap">
        <?= _t('Kreiran') ?>
        <?php if ($sort === 'created_at' && $dir === 'asc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=created_at&dir=desc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down"></i></a>
        <?php elseif ($sort === 'created_at' && $dir === 'desc'): ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=created_at&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-sort-alpha-down-alt"></i></a>
        <?php else: ?>
          <a href="<?= App::urlFor('admin.users') ?>?sort=created_at&dir=asc&per_page=<?= $perPage ?>&page=<?= $page ?>"><i class="bi bi-arrow-down-up text-muted"></i></a>
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
      $formatter = new IntlDateFormatter($fmtLocale, IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
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
        <td class="text-center text-nowrap"><form method="post"
                  action=""
                  id="radnja_<?= $korisnik['uuid'] ?>"
                  name="radnja_<?= $korisnik['uuid'] ?>">
            <input type="hidden" name="uuid" value="<?= htmlspecialchars($korisnik['uuid']) ?>">
            <input type="hidden" name="radnja" value="">
          </form>
          <button type="submit" form="radnja_<?= $korisnik['uuid'] ?>" name="radnja" value="edit" class="btn btn-sm border-0 bg-transparent p-0 text-secondary"><i class="bi bi-pencil-square"></i></button>
        </td>
        <td class="text-nowrap"><?= htmlspecialchars($korisnik['ime']) ?></td>
        <td class="text-nowrap"><?= htmlspecialchars($korisnik['prezime']) ?></td>
        <td class="text-nowrap"><?= htmlspecialchars($korisnik['korisnicko_ime']) ?></td>
        <td class="text-nowrap"><?= htmlspecialchars($korisnik['role']) ?></td>
        <td class="text-nowrap"><?= htmlspecialchars($korisnik['email']) ?></td>
        <td class="text-nowrap"><?= htmlspecialchars($korisnik['oib']) ?></td>
        <td class="text-nowrap">
          <?php
            $timestamp = strtotime($korisnik['created_at']);
            echo $formatter->format($timestamp);
          ?>
        </td>
        <td class="text-center text-nowrap">
          <?php
          // HR: Gumbi za administrativne radnje (uredi, obriši, reset lozinke)
          // EN: Buttons for administrative actions (edit, delete, reset password)
          ?>
          <button type="button" class="btn btn-sm border-0 bg-transparent p-0 <?= $isSelf ? 'text-muted' : 'text-danger' ?>"
                  data-uuid="<?= $korisnik['uuid'] ?>"
                  data-name="<?= htmlspecialchars($korisnik['ime'].' '.$korisnik['prezime']) ?>"
                  data-action="delete"
                  <?= $isSelf ? 'disabled' : '' ?>
                  data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="bi bi-trash3"></i>
          </button>
          &nbsp;
          <button type="button" class="btn btn-sm border-0 bg-transparent p-0 <?= $isSelf ? 'text-muted' : 'text-warning' ?>"
                  data-uuid="<?= $korisnik['uuid'] ?>"
                  data-name="<?= htmlspecialchars($korisnik['ime'].' '.$korisnik['prezime']) ?>"
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
<?= new HelperController()->renderPagination($page, $perPage, $total, $sort, $dir, App::urlFor('admin.users')) ?>

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
            <input type="hidden" name="radnja" value="delete">
            <input type="hidden" name="per_page" value="<?= htmlspecialchars($perPage) ?>">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
            <button type="submit" class="btn btn-danger"><?= _t('Obriši') ?></button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _t('Odustani') ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="<?= App::url('js/popisKorisnika.php.js') ?>?v=<?= filemtime(__DIR__ . '/../public/js/adminUsers.js') ?>"></script>

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
            <input type="hidden" name="radnja" value="password_reset">
            <input type="hidden" name="per_page" value="<?= htmlspecialchars($perPage) ?>">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
            <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
            <button type="submit" class="btn btn-warning"><?= _t('Resetiraj lozinku') ?></button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= _t('Odustani') ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>

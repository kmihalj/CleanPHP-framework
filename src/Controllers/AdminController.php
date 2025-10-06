<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Kontroler za administracijske stranice.
 * Omogućuje prikaz popisa korisnika s paginacijom i sortiranjem,
 * obradu radnji nad korisnicima (brisanje, itd.),
 * te prikaz stranice za zabranjeni pristup.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Controller for administration pages.
 * Provides user listing with pagination and sorting,
 * handles user actions (delete, etc.),
 * and displays the forbidden access page.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Models\Korisnik;
use App\Models\Rola;
use PDO;
use PDOStatement;
use RuntimeException;
use App\Core\Mailer;
use Exception;

class AdminController extends Controller
{
  protected PDO $pdo;

  /**
   * HR: Konstruktor inicijalizira PDO vezu za bazu podataka.
   * EN: Constructor initializes PDO connection for the database.
   *
   * @return void
   */
  public function __construct()
  {
    $db = require __DIR__ . '/../../config/database.php';
    $this->pdo = $db['pdo'];
  }

  /**
   * HR: Prikazuje stranicu za zabranjeni pristup.
   * EN: Displays the forbidden access page.
   *
   * @return void
   */
  public function forbidden(): void
  {
    $this->render('admin/forbidden', ['title' => _t('Nedozvoljen pristup')]);
  }

  /**
   * HR: Prikaz popisa korisnika s paginacijom i sortiranjem.
   * EN: Displays user listing with pagination and sorting.
   *
   * @return void
   */
  public function popisKorisnika(): void
  {
    // HR: Dozvoljene kolone za sortiranje / EN: Allowed columns for sorting
    $allowedColumns = [
      'ime' => 'korisnik.ime',
      'prezime' => 'korisnik.prezime',
      'korisnicko_ime' => 'korisnik.korisnicko_ime',
      'email' => 'korisnik.email',
      'role' => 'roles',
      'created_at' => 'korisnik.created_at'
    ];
    // HR: Dozvoljene opcije za broj po stranici / EN: Allowed per page options
    $perPageOptions = ['10', '25', 'all'];

    // HR: Get params from GET (or POST)
    // HR: Opcionalni parametar za pretragu korisnika / EN: Optional search parameter for filtering users
    $sort = $_GET['sort'] ?? 'prezime';
    $dir = strtolower($_GET['dir'] ?? 'asc');
    $perPage = $_GET['per_page'] ?? '10';
    $search = $_GET['search'] ?? '';

    // HR: Validacija broja po stranici / EN: Validate per page value
    if (!in_array($perPage, $perPageOptions, true)) {
      $perPage = '10';
    }

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    // HR: Ako je promijenjen broj po stranici, resetiraj na prvu stranicu / EN: If per_page changed, reset to first page
    if (isset($_GET['per_page_changed']) && $_GET['per_page_changed'] === '1') {
      $page = 1;
    }

    // HR: Validacija parametra sort / EN: Validate sort parameter
    if (!array_key_exists($sort, $allowedColumns)) {
      $sort = 'prezime';
    }
    // HR: Izgradi SQL upit s JOIN-om na role / EN: Build SQL query with JOIN on roles
    $dir = ($dir === 'desc') ? 'desc' : 'asc';

    // HR: Izgradi SQL upit s JOIN-om na korisnik_rola i role, te grupiranjem rola po korisniku
    $orderBy = $allowedColumns[$sort] . ' ' . strtoupper($dir);
    $sql = "SELECT korisnik.uuid AS uuid,
                   korisnik.ime,
                   korisnik.prezime,
                   korisnik.oib,
                   korisnik.korisnicko_ime,
                   korisnik.email,
                   korisnik.privremenaLozinka,
                   korisnik.created_at AS created_at,
                   GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ') AS roles,
                   GROUP_CONCAT(r.uuid ORDER BY r.name SEPARATOR ',') AS role_uuid
            FROM korisnik
            LEFT JOIN korisnik_rola kr ON korisnik.uuid = kr.korisnik_uuid
            LEFT JOIN role r ON kr.role_uuid = r.uuid";

    // HR: Dodaje uvjet pretrage u SQL upit ako je pretraga aktivna
    // EN: Adds search condition to SQL query if search is active
    if (!empty($search)) {
      $sql .= " WHERE ime LIKE :s1
              OR prezime LIKE :s2
              OR oib LIKE :s3
              OR korisnicko_ime LIKE :s4
              OR email LIKE :s5
              OR EXISTS (
                SELECT 1 FROM korisnik_rola kr2
                JOIN role r2 ON kr2.role_uuid = r2.uuid
                WHERE kr2.korisnik_uuid = korisnik.uuid AND r2.name LIKE :s6
              )";
    }

    $sql .= " GROUP BY korisnik.uuid";
    $sql .= " ORDER BY $orderBy";

    $limit = null;
    $offset = null;
    if ($perPage !== 'all') {
      $limit = (int)$perPage;
      $offset = ($page - 1) * $limit;
      $sql .= " LIMIT :limit OFFSET :offset";
    }

    // HR: Priprema i izvršavanje upita / EN: Prepare and execute query
    $stmt = $this->pdo->prepare($sql);

    // HR: Vezanje parametara pretrage na pripremljeni upit
    // EN: Bind search parameters to the prepared statement
    if (!empty($search)) {
      $this->bindSearchParams($stmt, $search);
    }
    if ($perPage !== 'all') {
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    $korisnici = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // HR: Ako je pretraga aktivna, ukupan broj korisnika računa se posebnim upitom s WHERE uvjetom.
    // EN: If search is active, total user count is calculated with a separate query including the WHERE condition.
    $korisnikModel = new Korisnik($this->pdo);
    if (!empty($search)) {
      $countSql = "SELECT COUNT(DISTINCT korisnik.uuid)
                   FROM korisnik
                   LEFT JOIN korisnik_rola kr ON korisnik.uuid = kr.korisnik_uuid
                   LEFT JOIN role r ON kr.role_uuid = r.uuid
                   WHERE ime LIKE :s1
                   OR prezime LIKE :s2
                   OR oib LIKE :s3
                   OR korisnicko_ime LIKE :s4
                   OR email LIKE :s5
                   OR EXISTS (
                      SELECT 1 FROM korisnik_rola kr2
                      JOIN role r2 ON kr2.role_uuid = r2.uuid
                      WHERE kr2.korisnik_uuid = korisnik.uuid AND r2.name LIKE :s6
                   )";
      $countStmt = $this->pdo->prepare($countSql);
      $this->bindSearchParams($countStmt, $search);
      $countStmt->execute();
      $total = (int)$countStmt->fetchColumn();
    } else {
      $total = $korisnikModel->countAll();
    }

    $roleModel = new Rola($this->pdo);
    $roleOptions = $roleModel->findAllRola();

    $params = [
      'title' => _t('Popis korisnika'),
      'korisnici' => $korisnici,
      'total' => $total,
      'perPage' => $perPage,
      'page' => $page,
      'perPageOptions' => $perPageOptions,
      'sort' => $sort,
      'dir' => $dir,
      'roleOptions' => $roleOptions
    ];
    // HR: Ako je pretraga aktivna, proslijedi parametar pretrage u prikaz
    // EN: If search is active, pass the search parameter to the view
    if (!empty($search)) {
      $params['search'] = $search;
    }
    $this->render('admin/popisKorisnika', $params);
  }

  /**
   * HR: Briše korisnika po UUID-u.
   * EN: Deletes a user by UUID.
   *
   * @return void
   * @throws RuntimeException HR: Ako dođe do greške prilikom brisanja korisnika.
   * EN: If an error occurs during user deletion.
   */
  public function brisanjeKorisnika(): void
  {
    $params = $this->validateAndGetPostParams();
    extract($params);

    // HR: Prvo brišemo sve uloge dodijeljene korisniku iz tablice korisnik_rola.
    // EN: First, delete all roles assigned to the user from korisnik_rola table.
    $deleteRolesStmt = $this->pdo->prepare("DELETE FROM korisnik_rola WHERE korisnik_uuid = :uuid");
    $deleteRolesStmt->bindValue(':uuid', $uuid);
    $deleteRolesStmt->execute();

    // HR: Zatim brišemo samog korisnika.
    // EN: Then, delete the user itself.
    $deleted = $korisnikModel->deleteByUuid($uuid);

    if ($deleted) {
      $ime = $deleted['ime'];
      $prezime = $deleted['prezime'];
      flash_set('success', sprintf(_t("Korisnik: %s %s je uspješno obrisan"), $ime, $prezime));

      // HR: Provjeri treba li smanjiti broj stranice nakon brisanja / EN: Check if page number should decrease after deletion
      if ($perPage !== 'all') {
        if (!empty($search)) {
          // HR: Ako postoji pretraga, računaj broj preostalih rezultata s WHERE uvjetom
          // EN: If search is active, count remaining results with WHERE condition
          $countSql = "SELECT COUNT(DISTINCT korisnik.uuid)
                       FROM korisnik
                       LEFT JOIN korisnik_rola kr ON korisnik.uuid = kr.korisnik_uuid
                       LEFT JOIN role r ON kr.role_uuid = r.uuid
                       WHERE ime LIKE :s1
                       OR prezime LIKE :s2
                       OR oib LIKE :s3
                       OR korisnicko_ime LIKE :s4
                       OR email LIKE :s5
                       OR EXISTS (
                          SELECT 1 FROM korisnik_rola kr2
                          JOIN role r2 ON kr2.role_uuid = r2.uuid
                          WHERE kr2.korisnik_uuid = korisnik.uuid AND r2.name LIKE :s6
                       )";
          $countStmt = $this->pdo->prepare($countSql);
          $this->bindSearchParams($countStmt, $search);
          $countStmt->execute();
          $total = (int)$countStmt->fetchColumn();
        } else {
          // HR: Bez pretrage, broj korisnika računa se klasično
          // EN: Without search, count all users normally
          $total = $korisnikModel->countAll();
        }

        $offset = ($page - 1) * (int)$perPage;
        if ($offset >= $total && $page > 1) {
          $page--;
        }
      }
    } else {
      // HR: Postavi flash poruku o grešci kada korisnik nije pronađen
      // EN: Set flash error message when user is not found
      flash_set('error', _t("Korisnik nije pronađen."));
    }

    // HR: Preusmjeri natrag na popis korisnika s parametrima / EN: Redirect back to user listing with parameters
    $this->redirectToUserList($perPage, $sort, $dir, $page, $search);
  }

  /**
   * HR: Resetira lozinku korisnika na privremenu, šalje email s novom lozinkom.
   * EN: Resets user's password to a temporary one, sends email with new password.
   *
   * @return void
   * @throws RuntimeException HR: Ako dođe do greške prilikom resetiranja lozinke.
   * EN: If an error occurs during password reset.
   */
  public function resetLozinkeKorisnika(): void
  {
    $params = $this->validateAndGetPostParams();
    extract($params);

    $tempPassword = $korisnikModel->setTemporaryPassword($uuid);

      // Dohvati korisnika radi slanja emaila
      $korisnik = $korisnikModel->findKorisnik('uuid', $uuid);
      if ($korisnik && !empty($korisnik->email)) {
        $config = require __DIR__ . '/../../config/mail.php';
        $sent = false;
        try {
          $mailer = new Mailer($config);
          $to = $korisnik->email;
          $subject = _t('Resetiranje lozinke');
          $bodyHtml = sprintf(
            _t("Poštovani,<br><br>Vaša nova privremena lozinka je: <strong>%s</strong><br>Korisničko ime: <strong>%s</strong><br>Molimo prijavite se i promijenite lozinku.<br><br>Lijep pozdrav."),
            htmlspecialchars($tempPassword, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($korisnik->korisnicko_ime, ENT_QUOTES, 'UTF-8')
          );
          $bodyText = sprintf(
            _t("Poštovani,\n\nVaša nova privremena lozinka je: %s\nKorisničko ime: %s\nMolimo prijavite se i promijenite lozinku.\n\nLijep pozdrav."),
            $tempPassword,
            $korisnik->korisnicko_ime
          );
          $sent = $mailer->send($to, $subject, $bodyHtml, $bodyText);
        } catch (Exception) {
          flash_set('error', _t('Došlo je do greške prilikom slanja e-maila. Pokušajte ponovo.'));
          $this->redirectToUserList($perPage, $sort, $dir, $page, $search);
        }

        if ($sent) {
          flash_set('success', _t("Privremena lozinka je uspješno generirana i poslana korisniku."));
        } else {
          flash_set('error', _t('Slanje e-maila nije uspjelo. Pokušajte ponovo.'));
        }
      }
    else {
      flash_set('error', _t("Nije moguće resetirati lozinku za korisnika."));
    }
    $this->redirectToUserList($perPage, $sort, $dir, $page, $search);
  }

  /**
   * HR: Uređuje korisnika s validacijom podataka.
   * EN: Edits a user with data validation.
   *
   * @return void
   */
  public function editKorisnika(): void
  {
    $params = $this->validateAndGetPostParams();
    extract($params);

    $korisnik = $korisnikModel->findKorisnik('uuid', $uuid);

    // HR: Dohvati POST podatke / EN: Get POST data
    $ime = trim($_POST['editIme'] ?? '');
    $prezime = trim($_POST['editPrezime'] ?? '');
    $oib = trim($_POST['editOib'] ?? '');
    $korisnicko_ime = trim($_POST['editUsername'] ?? '');
    $email = trim($_POST['editEmail'] ?? '');
    $editRoles = $_POST['editRole'] ?? [];
    if (!is_array($editRoles)) {
      $editRoles = [$editRoles];
    }

    $errors = [];

    // HR: Provjera OIB-a pomoću ugrađene metode / EN: Check OIB using built-in method
    if ($oib !== $korisnik->oib && !AuthController::check($oib)) {
      $errors['editOib'] = _t('OIB nije ispravan.');
    }

    // HR: Provjera zauzetosti korisničkog imena samo ako je promijenjeno / EN: Check if username is taken only if changed
    if ($korisnicko_ime !== $korisnik->korisnicko_ime && $korisnikModel->existsByField('korisnicko_ime', $korisnicko_ime, $uuid)) {
      $errors['editUsername'] = _t('Korisničko ime je već zauzeto.');
    }

    // HR: Provjera zauzetosti emaila samo ako je promijenjen / EN: Check if email is taken only if changed
    if ($email !== $korisnik->email && $korisnikModel->existsByField('email', $email, $uuid)) {
      $errors['editEmail'] = _t('Email je već zauzet.');
    }

    // HR: Provjera da je odabrana barem jedna rola / EN: Check that at least one role is selected
    if (empty($editRoles)) {
      $errors['editRole'] = _t('Korisnik mora imati barem jednu rolu.');
    }

    if (!empty($errors)) {
      // HR: Postavi flash greške i stare unose, te preusmjeri nazad / EN: Set flash errors and old input, then redirect back
      flash_set('errors', $errors);
      flash_set('old_input', $_POST);
      $this->redirectToUserList($perPage, $sort, $dir, $page, $search);
    }

    // HR: Pripremi podatke za ažuriranje (bez role_uuid) / EN: Prepare data for update (without role_uuid)
    $data = [
      'ime' => $ime,
      'prezime' => $prezime,
      'oib' => $oib,
      'korisnicko_ime' => $korisnicko_ime,
      'email' => $email,
    ];

    // HR: Pokušaj ažurirati korisnika / EN: Attempt to update user
    $updated = $korisnikModel->update($uuid, $data);

    // HR: Briši sve postojeće uloge korisnika i dodaj nove / EN: Delete all current user roles and insert new ones
    $deleteStmt = $this->pdo->prepare("DELETE FROM korisnik_rola WHERE korisnik_uuid = :uuid");
    $deleteStmt->bindValue(':uuid', $uuid);
    $deleteStmt->execute();

    $successRoles = true;
    foreach ($editRoles as $roleUuid) {
      $insertStmt = $this->pdo->prepare("INSERT INTO korisnik_rola (korisnik_uuid, role_uuid) VALUES (:korisnik_uuid, :role_uuid)");
      $successRoles = $successRoles && $insertStmt->execute([
        ':korisnik_uuid' => $uuid,
        ':role_uuid' => $roleUuid
      ]);
    }

    if ($updated && $successRoles) {
      // HR: Uspješno ažuriranje / EN: Successful update
      flash_set('success', sprintf(_t("Korisnik: %s %s je uspješno ažuriran."), $ime, $prezime));
    } else {
      // HR: Neuspješno ažuriranje / EN: Update failed
      flash_set('error', sprintf(_t("Ažuriranje korisnika: %s %s nije uspjelo."), $ime, $prezime));
    }
    // HR: Preusmjeri na popis korisnika / EN: Redirect to user listing
    $this->redirectToUserList($perPage, $sort, $dir, $page, $search);
  }

  /**
   * HR: HELPER Preusmjerava na popis korisnika s očuvanim parametrima, izuzimajući default vrijednosti.
   * EN: HELPER Redirects back to the user listing with preserved parameters, omitting defaults.
   *
   * @param string $perPage HR: Broj stavki po stranici (ili 'all') / EN: Items per page (or 'all')
   * @param string $sort HR: Kolona po kojoj se sortira / EN: Column to sort by
   * @param string $dir HR: Smjer sortiranja (asc/desc) / EN: Sort direction (asc/desc)
   * @param int $page HR: Trenutni broj stranice / EN: Current page number
   * @param string $search HR: pojam za pretragu / EN: search argument
   * @return void
   */
  private function redirectToUserList(string $perPage, string $sort, string $dir, int $page, string $search): void
  {
    // HR: Preusmjeri natrag na popis korisnika s parametrima / EN: Redirect back to user listing with parameters
    $params = [
      'per_page' => $perPage,
      'sort' => $sort,
      'dir' => $dir,
      'page' => $page,
    ];
    if (!empty($search)) {
      $params['search'] = $search;
    }
    $query = http_build_query($params);

    if ($perPage === '10' && $sort === 'prezime' && $dir === 'asc' && $page === 1 && empty($search)) {
      $query = '';
    }

    header('Location: ' . App::urlFor('admin.users') . ($query !== '' ? '?' . $query : ''));
    exit;
  }

  /**
   * HR: HELPER Validira POST zahtjev i dohvaća potrebne parametre za administraciju korisnika.
   * EN: HELPER Validates POST request and retrieves necessary parameters for user administration.
   *
   * @return array{
   *   uuid: string,
   *   perPage: string,
   *   sort: string,
   *   dir: string,
   *   page: int,
   *   search: string,
   *   korisnikModel: Korisnik
   * }
   * @throws RuntimeException Ako zahtjev nije POST ili UUID nije postavljen.
   */
  private function validateAndGetPostParams(): array
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: ' . App::urlFor('admin.users'));
      exit;
    }

    $uuid = $_POST['uuid'] ?? null;
    if (!$uuid) {
      throw new RuntimeException('UUID nije postavljen.');
    }

    $perPage = $_POST['per_page'] ?? '10';
    $sort = $_POST['sort'] ?? 'prezime';
    $dir = strtolower($_POST['dir'] ?? 'asc');
    $page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
    $search = $_POST['search'] ?? null;

    $korisnikModel = new Korisnik($this->pdo);

    return [
      'uuid' => $uuid,
      'perPage' => $perPage,
      'sort' => $sort,
      'dir' => $dir,
      'page' => $page,
      'search' => $search ?? '',
      'korisnikModel' => $korisnikModel,
    ];
  }

  /**
   * HR: HELPER Metoda za vezanje parametara pretrage na PDOStatement.
   * EN: HELPER Method to bind search parameters to PDOStatement.
   *
   * @param PDOStatement $stmt
   * @param string $search
   * @return void
   */
  private function bindSearchParams(PDOStatement $stmt, string $search): void
  {
    $like = '%' . $search . '%';
    $stmt->bindValue(':s1', $like);
    $stmt->bindValue(':s2', $like);
    $stmt->bindValue(':s3', $like);
    $stmt->bindValue(':s4', $like);
    $stmt->bindValue(':s5', $like);
    $stmt->bindValue(':s6', $like);
  }
}

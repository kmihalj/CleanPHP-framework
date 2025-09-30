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
use PDO;
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

    $deleted = $korisnikModel->deleteByUuid($uuid);

    if ($deleted) {
      $ime = $deleted['ime'];
      $prezime = $deleted['prezime'];
      flash_set('success', sprintf(_t("Korisnik: %s %s je uspješno obrisan"), $ime, $prezime));

      // HR: Provjeri treba li smanjiti broj stranice nakon brisanja / EN: Check if page number should decrease after deletion
      if ($perPage !== 'all') {
        $total = $korisnikModel->countAll();
        $offset = ($page - 1) * (int)$perPage;
        if ($offset >= $total && $page > 1) {
          $page--;
        }
      }
    } else {
      flash_set('error', _t("Korisnik nije pronađen."));
    }

    // HR: Preusmjeri natrag na popis korisnika s parametrima / EN: Redirect back to user listing with parameters
    $this->redirectToUserList($perPage, $sort, $dir, $page);
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
      $korisnik = $korisnikModel->findByField('uuid', $uuid);
      if ($korisnik && !empty($korisnik['email'])) {
        $config = require __DIR__ . '/../../config/mail.php';
        $sent = false;
        try {
          $mailer = new Mailer($config);
          $to = $korisnik['email'];
          $subject = _t('Resetiranje lozinke');
          $bodyHtml = sprintf(
            _t("Poštovani,<br><br>Vaša nova privremena lozinka je: <strong>%s</strong><br>Korisničko ime: <strong>%s</strong><br>Molimo prijavite se i promijenite lozinku.<br><br>Lijep pozdrav."),
            htmlspecialchars($tempPassword, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($korisnik['korisnicko_ime'], ENT_QUOTES, 'UTF-8')
          );
          $bodyText = sprintf(
            _t("Poštovani,\n\nVaša nova privremena lozinka je: %s\nKorisničko ime: %s\nMolimo prijavite se i promijenite lozinku.\n\nLijep pozdrav."),
            $tempPassword,
            $korisnik['korisnicko_ime']
          );
          $sent = $mailer->send($to, $subject, $bodyHtml, $bodyText);
        } catch (Exception) {
          flash_set('error', _t('Došlo je do greške prilikom slanja e-maila. Pokušajte ponovo.'));
          $this->redirectToUserList($perPage, $sort, $dir, $page);
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
    $this->redirectToUserList($perPage, $sort, $dir, $page);
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
      'role' => 'r.name',
      'created_at' => 'korisnik.created_at'
    ];
    // HR: Dozvoljene opcije za broj po stranici / EN: Allowed per page options
    $perPageOptions = ['10', '25', 'all'];

    // HR: Get params from GET (or POST)
    $sort = $_GET['sort'] ?? 'prezime';
    $dir = strtolower($_GET['dir'] ?? 'asc');
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = $_GET['per_page'] ?? '10';

    // HR: Validacija parametra sort / EN: Validate sort parameter
    if (!array_key_exists($sort, $allowedColumns)) {
      $sort = 'prezime';
    }
    // HR: Validacija smjera sortiranja / EN: Validate sort direction
    $dir = ($dir === 'desc') ? 'desc' : 'asc';
    // HR: Validacija broja po stranici / EN: Validate per page value
    if (!in_array($perPage, $perPageOptions, true)) {
      $perPage = '10';
    }

    // HR: Izgradi SQL upit s JOIN-om na role / EN: Build SQL query with JOIN on roles
    $orderBy = $allowedColumns[$sort] . ' ' . strtoupper($dir);
    $sql = "SELECT korisnik.uuid as uuid, ime, prezime, oib, korisnicko_ime, email, privremenaLozinka, korisnik.created_at as created_at, r.name AS role
                FROM korisnik
                LEFT JOIN role AS r ON korisnik.role_uuid = r.uuid
                ORDER BY $orderBy";
    if ($perPage !== 'all') {
      $limit = (int)$perPage;
      $offset = ($page - 1) * $limit;
      $sql .= " LIMIT :limit OFFSET :offset";
    }

    // HR: Priprema i izvršavanje upita / EN: Prepare and execute query
    $stmt = $this->pdo->prepare($sql);
    if ($perPage !== 'all') {
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    $korisnici = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // HR: Ukupan broj korisnika za paginaciju / EN: Total number of users for pagination
    $korisnikModel = new Korisnik($this->pdo);
    $total = $korisnikModel->countAll();

    $this->render('admin/popisKorisnika', [
      'title' => _t('Popis korisnika'),
      'korisnici' => $korisnici,
      'total' => $total,
      'perPage' => $perPage,
      'page' => $page,
      'perPageOptions' => $perPageOptions,
      'sort' => $sort,
      'dir' => $dir
    ]);
  }


  /**
   * HR: Preusmjerava na popis korisnika s očuvanim parametrima, izuzimajući default vrijednosti.
   * EN: Redirects back to the user listing with preserved parameters, omitting defaults.
   *
   * @param string $perPage HR: Broj stavki po stranici (ili 'all') / EN: Items per page (or 'all')
   * @param string $sort HR: Kolona po kojoj se sortira / EN: Column to sort by
   * @param string $dir HR: Smjer sortiranja (asc/desc) / EN: Sort direction (asc/desc)
   * @param int $page HR: Trenutni broj stranice / EN: Current page number
   * @return void
   */
  private function redirectToUserList(string $perPage, string $sort, string $dir, int $page): void
  {
    // HR: Preusmjeri natrag na popis korisnika s parametrima / EN: Redirect back to user listing with parameters
    $query = http_build_query([
      'per_page' => $perPage,
      'sort' => $sort,
      'dir' => $dir,
      'page' => $page,
    ]);

    if ($perPage === '10' && $sort === 'prezime' && $dir === 'asc' && $page === 1) {
      $query = '';
    }

    header('Location: ' . App::urlFor('admin.users') . ($query !== '' ? '?' . $query : ''));
    exit;
  }

  /**
   * HR: Validira POST zahtjev i dohvaća potrebne parametre za administraciju korisnika.
   * EN: Validates POST request and retrieves necessary parameters for user administration.
   *
   * @return array{
   *   uuid: string,
   *   perPage: string,
   *   sort: string,
   *   dir: string,
   *   page: int,
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

    $korisnikModel = new Korisnik($this->pdo);

    return [
      'uuid' => $uuid,
      'perPage' => $perPage,
      'sort' => $sort,
      'dir' => $dir,
      'page' => $page,
      'korisnikModel' => $korisnikModel,
    ];
  }
}

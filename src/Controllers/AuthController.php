<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Models\User;
use Exception;
use PDO;
use PDOException;

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Ovo je kontroler za autentikaciju korisnika.
 * Omogućuje prikaz login forme, prijavu, registraciju i odjavu.
 * Koristi model `User` za rad s bazom.
 * Provodi CSRF validaciju i koristi sesiju za pohranu user_id.
 *
 * AuthController upravlja svim procesima autentikacije korisnika:
 * prikaz forme za prijavu, obrada prijave, registracija novog korisnika i odjava.
 * Koristi model User za interakciju s bazom podataka kako bi upravljao korisničkim podacima.
 *
 * ===========================
 *  English
 * ===========================
 * This is the controller for user authentication.
 * Provides login form display, login, registration, and logout.
 * Uses `User` model for database interactions.
 * Performs CSRF validation and uses session to store user_id.
 *
 * The AuthController handles all authentication processes including displaying the login form,
 * processing login, user registration, and logout.
 * It uses the User model to interact with the database to manage user data.
 */
class AuthController extends Controller
{
  private User $userModel;

  public function __construct(PDO $pdo)
  { // Konstruktor prima PDO konekciju i stvara instancu modela User. / Constructor accepts a PDO connection and initializes the User model.
    $this->userModel = new User($pdo);
  }

  /**
   * Prikazuje formu za registraciju korisnika.
   * Shows the registration form.
   */
  public function registerForm(): void
  {
    $errors = flash_get('errors', []);
    $old    = flash_get('old', []);
    $this->render('auth/registracija', [
      'title'  => _t('Registracija'),
      'errors' => $errors,
      'old'    => $old,
    ]);
  }

  public function loginForm(): void
  { // Prikaže login formu ako korisnik nije prijavljen (render view 'auth/login'), inače ga preusmjeri na početnu stranicu. / Shows login form (renders view 'auth/login') if user not logged in, otherwise redirects to home page.
    if (!empty($_SESSION['user_id'])) {
      $this->redirect(App::url('dashboard'));
    }
    $errors = flash_get('errors', []);
    $old = flash_get('old', []);
    $this->render('auth/login', ['title' => _t('Prijava'), 'errors' => $errors, 'old' => $old]);
  }

  public function login(): void
  { // Validira CSRF token, provjerava korisničko ime i lozinku; na uspjeh preusmjerava na home, na grešku renderira 'auth/login' s porukom. / Validates CSRF token, checks username and password; on success redirects to home, on error renders 'auth/login' with error message.
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
      $this->render('auth/login', ['title' => _t('Prijava'), 'error' => _t('Nevažeći CSRF token.')]);
      return;
    }

    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $user = $this->userModel->findByUsername($username);
    if ($user && password_verify($password, $user['password'])) {
      // Provjera je li korisniku postavljeno password_reset (privremena lozinka) / Check if user must change temporary password
      if (isset($user['password_reset']) && $user['password_reset'] == 1) {
        $this->userModel->setSessionUserData($user);
        // Postavi flash poruku password_reset i redirectaj / Set flash message and redirect
        flash_set('password_reset', 1);
        header('Location: ' . App::url('change-password'));
        exit;
      }
      $this->userModel->setSessionUserData($user);
      $this->redirect(App::url('dashboard'));
    }
    $this->render('auth/login', ['title' => _t('Prijava'), 'error' => _t('Neispravno korisničko ime ili lozinka.')]);
  }

  public function register(): void
  { // Validira CSRF, provjerava polja, uspoređuje lozinke; na uspjeh renderira login view s porukom o uspjehu, na grešku renderira register view s porukom o grešci. / Validates CSRF, checks fields, compares passwords; on success renders login view with success message, on error renders register view with error message.
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
      $this->render('auth/registracija', ['title' => _t('Registracija'), 'error' => _t('Nevažeći CSRF token.')]);
      return;
    }

    $data = [
      'first_name' => trim($_POST['first_name'] ?? ''),
      'last_name' => trim($_POST['last_name'] ?? ''),
      'username' => trim($_POST['username'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
      'password' => (string)($_POST['password'] ?? ''),
      'password_confirm' => (string)($_POST['password_confirm'] ?? ''),
      'role' => 'Registriran',
    ];

    $errors = [];

    // Required fields
    foreach (['first_name', 'last_name', 'username', 'email', 'password', 'password_confirm'] as $f) {
      if (empty($data[$f])) {
        $errors[$f] = _t('Ovo polje je obavezno');
      }
    }

    // Email format
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = _t('Neispravan format email adrese');
    }

    // Password length
    if (!empty($data['password']) && strlen($data['password']) < 6) {
      $errors['password'] = _t('Lozinka mora imati najmanje 6 znakova');
    }

    // Password confirmation
    if (!empty($data['password']) && $data['password'] !== $data['password_confirm']) {
      $errors['password_confirm'] = _t('Lozinke se ne podudaraju');
    }

    // Unique constraints
    try {
      if (!empty($data['username']) && $this->userModel->existsBy('username', $data['username'])) {
        $errors['username'] = _t('Korisničko ime je zauzeto');
      }
      if (!empty($data['email']) && $this->userModel->existsBy('email', $data['email'])) {
        $errors['email'] = _t('Email je već registriran');
      }
    } catch (PDOException $e) {
      error_log(_t('Greška pri validaciji') . ': ' . $e->getMessage());
    }

    if (!empty($errors)) {
      // Sačuvaj stare vrijednosti (bez lozinki) i greške pa se vrati na formu
      flash_set('errors', $errors);
      flash_set('old', [
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'username' => $data['username'],
        'email' => $data['email'],
      ]);
      header('Location: ' . App::urlFor('register.form'));
      exit;
    }

    if ($data['password'] !== $data['password_confirm']) {
      $this->render('auth/registracija', ['title' => _t('Registracija'), 'error' => _t('Lozinke se ne podudaraju.')]);
      return;
    }
    if (!$data['first_name'] || !$data['last_name'] || !$data['username'] || !$data['email'] || !$data['password']) {
      $this->render('auth/registracija', ['title' => _t('Registracija'), 'error' => _t('Sva polja su obavezna.')]);
      return;
    }

    try {
      $ok = $this->userModel->create($data);
    } catch (PDOException $e) {
      error_log(_t('Registracija korisnika nije uspjela') . ': ' . $e->getMessage());
      $ok = false;
    }

    if ($ok) {
      $this->render('auth/login', ['title' => _t('Prijava'), 'error' => _t('Registracija uspješna. Prijavite se.')]);
      return;
    }
    $this->render('auth/registracija', ['title' => _t('Registracija'), 'error' => _t('Greška pri registraciji (korisničko ime ili email već postoji?).')]);
  }

  public function popis(): void
  { // Prikazuje popis svih korisnika koristeći view 'auth/popis'. / Displays a list of all users using the 'auth/popis' view.
    $validSort = ['id', 'first_name', 'last_name', 'username', 'email', 'created_at', 'role'];
    $sort = $_GET['sort'] ?? 'id';
    $dir = strtolower($_GET['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
    if (!in_array($sort, $validSort, true)) {
      $sort = 'id';
    }

    $perPageOptions = [10, 25, 50, 100, 'all'];
    $perPage = $_GET['per_page'] ?? 25;
    if ($perPage !== 'all') {
      $perPage = (int)$perPage;
      if (!in_array($perPage, [10, 25, 50, 100], true)) {
        $perPage = 25;
      }
    }
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($perPage === 'all') ? 0 : ($page - 1) * $perPage;

    try {
      $total = $this->userModel->countAll();
      if ($perPage === 'all') {
        $users = $this->userModel->all($sort, $dir);
      } else {
        $users = $this->userModel->paginate($perPage, $offset, $sort, $dir);
      }
    } catch (PDOException $e) {
      error_log(_t('Greška pri dohvaćanju korisnika') . ': ' . $e->getMessage());
      $users = [];
      $total = 0;
    }

    $this->render('auth/popis', [
      'title' => _t('Popis korisnika'),
      'users' => $users,
      'total' => $total,
      'page' => $page,
      'perPage' => $perPage,
      'dir' => $dir,
      'sort' => $sort,
      'perPageOptions' => $perPageOptions
    ]);
  }

  public function changePasswordForm(): void
  { // Prikazuje formu za promjenu lozinke / Displays the password change form
    $errors = flash_get('errors', []);
    $success = flash_get('success');
    $error = flash_get('error');
    $password_reset = flash_get('password_reset', $_SESSION['password_reset'] ?? 0);
    $this->render('auth/promjenaLozinke', [
      'title'   => _t('Promjena lozinke'),
      'errors'  => $errors,
      'success' => $success,
      'error'   => $error,
      'password_reset' => $password_reset,
    ]);
  }

  /**
   * Obrada promjene lozinke korisnika (samo za prijavljenog).
   * Handles password change for the logged-in user.
   */
  public function changePassword(): void
  {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
      flash_set('errors', ['csrf' => _t('Nevažeći CSRF token.')]);
      header('Location: ' . App::url('change-password'));
      exit;
    }

    // Dohvati trenutno prijavljenog korisnika / Get currently logged in user
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
      flash_set('errors', ['auth' => _t('Morate biti prijavljeni.')]);
      header('Location: ' . App::url('login'));
      exit;
    }

    $old_password = (string)($_POST['old_password'] ?? '');
    $new_password = (string)($_POST['new_password'] ?? '');
    $new_password_confirm = (string)($_POST['new_password_confirm'] ?? '');

    $errors = [];

    // Dohvati korisnika iz baze / Get user from database
    $user = $this->userModel->find($userId);
    if (!$user) {
      $errors['auth'] = _t('Korisnik nije pronađen.');
    }

    // Provjera stare lozinke / Check old password
    if (empty($old_password)) {
      $errors['old_password'] = _t('Unesite staru lozinku.');
    } elseif ($user && !password_verify($old_password, $user['password'])) {
      $errors['old_password'] = _t('Stara lozinka nije ispravna.');
    }

    // Provjera nove lozinke / Check new password
    $minLen = 8;
    if (empty($new_password)) {
      $errors['new_password'] = _t('Unesite novu lozinku.');
    } elseif (strlen($new_password) < $minLen) {
      $errors['new_password'] = sprintf(_t('Nova lozinka mora imati najmanje %d znakova.'), $minLen);
    }

    // Dodatna validacija: nova lozinka ne smije biti identična trenutnoj (privremenoj) / Extra validation: new password must not be identical to current (temporary) password
    if ($user && password_verify($new_password, $user['password'])) {
      $errors['new_password'] = _t('Nova lozinka ne smije biti ista kao trenutna privremena lozinka.');
    }

    // Provjera potvrde / Check confirmation
    if ($new_password !== $new_password_confirm) {
      $errors['new_password_confirm'] = _t('Nove lozinke se ne podudaraju.');
    }

    if (!empty($errors)) {
      $password_reset = flash_get('password_reset', $_SESSION['password_reset'] ?? 0);
      $this->render('auth/promjenaLozinke', [
        'title' => _t('Promjena lozinke'),
        'errors' => $errors,
        'password_reset' => $password_reset,
      ]);
      return;
    }

    // Sve ok, ažuriraj lozinku / All good, update password
    $newPasswordHash = password_hash($new_password, PASSWORD_DEFAULT);
    try {
      $updated = $this->userModel->updatePassword($userId, $newPasswordHash);
      if ($updated) {
        // Resetiraj password_reset polje na 0 nakon uspješne promjene lozinke
        // Reset the password_reset field to 0 after successful password change
        $this->userModel->clearPasswordReset($userId);
      }
    } catch (PDOException $e) {
      error_log(_t('Greška pri promjeni lozinke') . ': ' . $e->getMessage());
      $updated = false;
    }
    if ($updated) {
      // Očisti podatke iz sesije, ali zadrži flash poruke / Clear session data but keep flash messages
      $this->userModel->clearSessionUserData();
      Csrf::invalidate();
      flash_set('success', _t('Lozinka uspješno promijenjena, molim prijavite se ponovo.'));
      header('Location: ' . App::url('login'));
    } else {
      flash_set('errors', ['db' => _t('Greška pri spremanju nove lozinke.')]);
      header('Location: ' . App::url('change-password'));
    }
    exit;
  }

  /**
   * Prikazuje formu za zaboravljenu lozinku.
   * Shows the forgot password form.
   */
  public function forgotPasswordForm(): void
  {
    $errors = flash_get('errors', []);
    $this->render('auth/zaboravljenaLozinka', [
      'title'  => _t('Zaboravljena lozinka'),
      'errors' => $errors,
    ]);
  }

  /**
   * Obrada zahtjeva za zaboravljenu lozinku.
   * Handles forgot password POST request.
   */
  public function forgotPassword(): void
  {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
      flash_set('errors', ['csrf' => _t('Nevažeći CSRF token.')]);
      header('Location: ' . App::url('forgot-password'));
      exit;
    }

    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
      flash_set('errors', ['email' => _t('E-mail je obavezan.')]);
      header('Location: ' . App::url('forgot-password'));
      exit;
    }

    $user = $this->userModel->findByEmail($email);
    if (!$user) {
      flash_set('errors', ['email' => _t('Korisnik s tom e-mail adresom ne postoji.')]);
      header('Location: ' . App::url('forgot-password'));
      exit;
    }

    // Pozovi postojeću metodu za resetiranje lozinke i slanje e-maila
    // Ovdje privremeno "fake" $_POST['csrf'] za resetPassword jer očekuje CSRF u POST-u
    $_POST['csrf'] = $_POST['csrf'] ?? Csrf::token();
    ob_start();
    $this->resetPassword((int)$user['id']);
    ob_end_clean();

    // Nakon resetiranja lozinke postavi password_reset=1 korisniku
    // After password reset, set password_reset=1 for the user
    $this->userModel->setPasswordReset((int)$user['id']);

    flash_set('success', _t('Nova lozinka je poslana na unesenu e-mail adresu, provjerite mail i prijavite se.'));
    header('Location: ' . App::url('login'));
    exit;
  }

  public function logout(): void
  { // Uništava CSRF token, briše sesiju i preusmjerava korisnika na početnu stranicu (App::url()). / Invalidates CSRF token, destroys session and redirects user to homepage (App::url()).
    Csrf::invalidate();
    unset($_SESSION['role']);
    session_destroy();
    $this->redirect(App::url());
  }

  public function update(int $id): void
  { // Ažurira korisnika putem AJAX poziva. / Updates user via AJAX request.
    header('Content-Type: application/json; charset=utf-8');

    if (!Csrf::validate($_POST['csrf'] ?? null)) {
      echo json_encode(['status' => 'error', 'message' => _t('Nevažeći CSRF token.')]);
      return;
    }

    $data = [];
    foreach (['first_name', 'last_name', 'username', 'email', 'role'] as $field) {
      if (isset($_POST[$field])) {
        $data[$field] = trim((string)$_POST[$field]);
      }
    }

    if (empty($data)) {
      echo json_encode(['status' => 'error', 'message' => _t('Nema podataka za ažuriranje.')]);
      return;
    }

    $errors = [];

    // Required fields
    foreach (['first_name', 'last_name', 'username', 'email'] as $f) {
      if (empty($data[$f])) {
        $errors[$f] = _t('Ovo polje je obavezno.');
      }
    }

    // Email format
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = _t('Neispravan format email adrese.');
    }

    // Unique constraints
    if (!empty($data['username']) && $this->userModel->existsOtherUserWithUsername($id, $data['username'])) {
      $errors['username'] = _t('Korisničko ime je zauzeto.');
    }
    if (!empty($data['email']) && $this->userModel->existsOtherUserWithEmail($id, $data['email'])) {
      $errors['email'] = _t('Email je već registriran.');
    }

    if (!empty($errors)) {
      echo json_encode(['status' => 'error', 'fields' => $errors]);
      return;
    }

    try {
      $ok = $this->userModel->updateUser($id, $data);
      if ($ok) {
        echo json_encode(['status' => 'ok', 'message' => _t('Korisnik uspješno ažuriran.')]);
      } else {
        echo json_encode(['status' => 'error', 'message' => _t('Ažuriranje korisnika nije uspjelo.')]);
      }
    } catch (PDOException $e) {
      error_log(_t('Greška pri ažuriranju korisnika') . ': ' . $e->getMessage());
      echo json_encode(['status' => 'error', 'message' => _t('Greška pri ažuriranju korisnika.')]);
    }
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Metoda za resetiranje lozinke korisnika.
   * Validira CSRF token, pronalazi korisnika po ID-u, generira novu lozinku,
   * ažurira je u bazi, te šalje email s novom lozinkom korisniku.
   * Vraća JSON odgovor s statusom i porukom.
   *
   * ===========================
   *  English
   * ===========================
   * Method to reset a user's password.
   * Validates CSRF token, finds user by ID, generates a new password,
   * updates it in the database, and sends an email with the new password to the user.
   * Returns a JSON response with status and message.
   */
  public function resetPassword(int $id): void
  {
    header('Content-Type: application/json; charset=utf-8');

    if (!Csrf::validate($_POST['csrf'] ?? null)) {
      echo json_encode(['status' => 'error', 'message' => _t('Nevažeći CSRF token.')]);
      return;
    }

    try {
      $user = $this->userModel->find($id);
      if (!$user) {
        echo json_encode(['status' => 'error', 'message' => _t('Korisnik nije pronađen.')]);
        return;
      }

      $newPassword = bin2hex(random_bytes(4));
      $hash = password_hash($newPassword, PASSWORD_DEFAULT);

      $updated = $this->userModel->updatePassword($id, $hash);
      if (!$updated) {
        error_log(_t('Nije moguće ažurirati lozinku za korisnika ID: ') . $id);
        echo json_encode(['status' => 'error', 'message' => _t('Nije moguće ažurirati lozinku.')]);
        return;
      }

      // Postavi password_reset na 1 nakon uspješnog resetiranja lozinke
      // Set password_reset to 1 after successful password reset
      $this->userModel->setPasswordReset($id);

      $config = require __DIR__ . '/../../config/mail.php';
      $mailer = new Mailer($config);

      $subject = _t('Resetiranje lozinke');
      $bodyHtml = sprintf(
        _t("<p>Poštovani,</p><p>Za korisničko ime <strong>%s</strong> postavljena je nova lozinka: <strong>%s</strong></p><p>Molimo promijenite lozinku nakon prijave.</p>"),
        $user['username'],
        $newPassword
      );
      $bodyText = sprintf(
        _t("Poštovani,\n\nZa korisničko ime %s postavljena je nova lozinka: %s\n\nMolimo promijenite lozinku nakon prijave."),
        $user['username'],
        $newPassword
      );

      $mailer->send($user['email'], $subject, $bodyHtml, $bodyText);

      echo json_encode([
        'status' => 'ok',
        'message' => sprintf(
          _t("Korisniku '%s' je generirana nova lozinka i poslana na e-mail adresu: '%s'"),
          $user['first_name'] . ' ' . $user['last_name'],
          $user['email']
        )
      ]);
    } catch (Exception $e) {
      error_log(_t('Greška pri resetiranju lozinke') . ': ' . $e->getMessage());
      echo json_encode(['status' => 'error', 'message' => _t('Greška pri resetiranju lozinke.')]);
    }
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Metoda za brisanje korisnika.
   * Validira CSRF token, briše korisnika po ID-u,
   * te preusmjerava natrag na popis korisnika s porukom.
   *
   * ===========================
   *  English
   * ===========================
   * Method to delete a user.
   * Validates CSRF token, deletes the user by ID,
   * and redirects back to the user list with a message.
   */
  public function delete(int $id): void
  {
    // Dohvati parametre iz POST-a (ili default vrijednosti)
    $sort = $_POST['sort'] ?? 'id';
    $dir = strtolower($_POST['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
    $page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;
    $per_page = $_POST['per_page'] ?? 25;
    // Ako CSRF nije ispravan
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
      flash_set('error', _t('Nevažeći CSRF token.'));
      $params = [
        'sort' => $sort,
        'dir' => $dir,
        'page' => $page,
        'per_page' => $per_page,
      ];
      $qs = http_build_query($params);
      header('Location: ' . App::url('admin/users') . ($qs ? ('?' . $qs) : ''));
      exit;
    }

    try {
      $ok = $this->userModel->deleteUser($id);
    } catch (PDOException $e) {
      error_log(_t('Greška pri brisanju korisnika') . ': ' . $e->getMessage());
      $ok = false;
    }

    if ($ok) {
      flash_set('success', _t('Korisnik je uspješno obrisan.'));
    } else {
      flash_set('error', _t('Brisanje korisnika nije uspjelo.'));
    }

    // Izračunaj ukupno korisnika nakon brisanja
    try {
      $totalUsers = $this->userModel->countAllUsers();
    } catch (PDOException $e) {
      error_log(_t('Greška pri brojanju korisnika') . ': ' . $e->getMessage());
      $totalUsers = 0;
    }
    // Izračunaj maksimalan broj stranica
    if ($per_page === 'all' || $per_page === null) {
      $maxPage = 1;
    } else {
      $perPageInt = (int)$per_page;
      $maxPage = max(1, (int)ceil($totalUsers / ($perPageInt > 0 ? $perPageInt : 25)));
    }
    if ($page > $maxPage) {
      $page = $maxPage;
    }
    $params = [
      'sort' => $sort,
      'dir' => $dir,
      'page' => $page,
      'per_page' => $per_page,
    ];
    $qs = http_build_query($params);
    header('Location: ' . App::url('admin/users') . ($qs ? ('?' . $qs) : ''));
    exit;
  }
}

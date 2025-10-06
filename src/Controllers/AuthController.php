<?php

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Kontroler za autentikaciju korisnika.
 * Omogućuje registraciju, prijavu, odjavu, resetiranje lozinke,
 * promjenu lozinke i validaciju OIB-a.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * User authentication controller.
 * Provides registration, login, logout, password reset,
 * password change and OIB validation.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Models\Korisnik;
use App\Models\KorisnikRola;
use App\Models\Rola;
use PDO;
use PHPMailer\PHPMailer\Exception;
use RuntimeException;

class AuthController extends Controller
{

  /**
   * HR: Konstruktor inicijalizira modele Role i Korisnik s PDO konekcijom.
   * EN: Constructor initializes Role and Korisnik models with PDO connection.
   *
   * @return void
   * @throws RuntimeException HR: Ako DB konfiguracija ne vraća PDO instancu. / EN: If DB config does not return a PDO instance.
   */
  private Rola $rola;
  private Korisnik $korisnik;

  private KorisnikRola $korisnikRola;

  public function __construct()
  {
    // Učitaj konfiguraciju baze; datoteka može vratiti PDO ili niz s 'pdo'
    $db = require __DIR__ . '/../../config/database.php';
    $pdo = ($db instanceof PDO) ? $db : ($db['pdo'] ?? null);

    if (!$pdo instanceof PDO) {
      throw new RuntimeException(_t('Neispravna DB konfiguracija: nedostaje PDO instanca'));
    }

    // konstruktori odrade migraciju i default zapise ako ih ima u modelu
    $this->rola = new Rola($pdo);
    $this->korisnik = new Korisnik($pdo);
    $this->korisnikRola = new KorisnikRola($pdo);
  }

  /**
   * HR: Prikazuje formu za registraciju korisnika.
   * EN: Renders the registration form.
   *
   * @return void
   */
  public function register(): void
  { // Renderira javnu početnu stranicu s naslovom. / Renders the public home page with a title.
    $this->render('korisnik/registracija', ['title' => _t('Registracija korisnika')]);
  }

  /**
   * HR: Obrada registracijskih podataka i kreiranje novog korisnika.
   * EN: Handles registration data and creates a new user.
   *
   * @return void
   */
  public function registerPOST(): void
  {
    $errors = [];

    // HR: Dohvati podatke iz POST zahtjeva / EN: Get data from POST request
    $ime = trim($_POST['ime'] ?? '');
    $prezime = trim($_POST['prezime'] ?? '');
    $korisnicko_ime = trim($_POST['korisnicko_ime'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $oib = trim($_POST['oib'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';
    $potvrda_lozinke = $_POST['potvrda_lozinke'] ?? '';

    // HR: Validacija lozinke / EN: Password validation
    if ($lozinka && strlen($lozinka) < 8) {
      $errors['lozinka'] = _t('Lozinka mora imati barem 8 znakova.');
    }
    if ($lozinka !== $potvrda_lozinke) {
      $errors['potvrda_lozinke'] = _t('Lozinke se ne podudaraju.');
      $errors['lozinka'] = _t('Lozinke se ne podudaraju.');
    }

    // HR: Validacija OIB-a / EN: OIB validation
    if ($oib && !self::check($oib)) {
      $errors['oib'] = _t('OIB nije valjan.');
    }

    // HR: Provjera jedinstvenosti korisničkog imena, OIB-a i emaila / EN: Check uniqueness of username, OIB and email
    if ($korisnicko_ime && $this->korisnik->existsByField('korisnicko_ime', $korisnicko_ime)) {
      $errors['korisnicko_ime'] = _t('Korisničko ime je već zauzeto.');
    }
    if ($oib && $this->korisnik->existsByField('oib', $oib)) {
      $errors['oib'] = _t('OIB je već registriran.');
    }
    if ($email && $this->korisnik->existsByField('email', $email)) {
      $errors['email'] = _t('E-mail je već registriran.');
    }

    // HR: Ako postoje greške, spremi ih u flash i preusmjeri natrag / EN: If errors exist, save to flash and redirect back
    if (!empty($errors)) {
      flash_set('error', _t('Korisnik nije kreiran, provjerite podatke.'));
      flash_set('errors', $errors);
      flash_set('old_input', $_POST);
      header('Location: ' . App::urlFor('register.form'));
      exit;
    }

    // HR: Odredi rolu prvog korisnika (Admin) ili kasnije (Registriran) / EN: Determine role (first user = Admin, others = Registered)
    $brojKorisnika = $this->korisnik->countAll();
    $rola = ($brojKorisnika === 0) ? 'Admin' : 'Registriran';
    $role = $this->rola->findRola('name', $rola);
    $role_uuid = $role?->uuid;

    // HR: Spremi korisnika u bazu (lozinka se hashira ovdje) i dohvati njegov UUID
    // EN: Save the user (password hashed here) and get the new user's UUID
    $korisnikUuid = $this->korisnik->create([
      'ime' => $ime,
      'prezime' => $prezime,
      'korisnicko_ime' => $korisnicko_ime,
      'email' => $email,
      'oib' => $oib,
      'lozinka' => password_hash($lozinka, PASSWORD_DEFAULT),
    ]);

    // HR: Poveži korisnika s početnom ulogom u pivot tablici korisnik_rola
    // EN: Link the user with the initial role in the pivot table korisnik_rola
    if (!empty($role_uuid)) {
      $this->korisnikRola->create([
        'korisnik_uuid' => $korisnikUuid,
        'role_uuid' => $role_uuid,
      ]);
    }

    flash_set('success', _t('Korisnik uspješno kreiran. Možete se prijaviti'));
    header('Location: ' . App::urlFor('login.form'));
    exit;
  }

  /**
   * HR: Prikazuje formu za login korisnika.
   * EN: Renders the login form.
   *
   * @return void
   */
  public function login(): void
  {
    $this->render('korisnik/prijava', ['title' => _t('Prijava korisnika')]);
  }

  /**
   * HR: Obrada prijave korisnika.
   * EN: Handles user login.
   *
   * @return void
   */
  public function loginPOST(): void
  {
    // HR: Pokreni sesiju ako nije aktivna / EN: Start session if not active
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $korisnicko_ime = trim($_POST['korisnicko_ime'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';

    // HR: Dohvati korisnika iz baze / EN: Fetch user from DB
    $korisnik = $this->korisnik->findKorisnik('korisnicko_ime', $korisnicko_ime);
    $hash = $korisnik->lozinka ?? '';

    // HR: Provjeri lozinku / EN: Verify password
    if (!$korisnik || !password_verify($lozinka, $hash)) {
      flash_set('error', _t('Prijava nije uspjela, provjerite korisničko ime i lozinku'));
      header('Location: ' . App::urlFor('login.form'));
      exit;
    }

    // HR: Dohvati sve role za korisnika iz pivot tablice korisnik_rola
    $korisnik_roles = $this->korisnikRola->findAllByKorisnikUuid($korisnik->uuid);
    $roles = [];
    if (!empty($korisnik_roles)) {
      foreach ($korisnik_roles as $kr) {
        $rola = $this->rola->findRola('uuid', $kr->role_uuid);
        if ($rola) {
          $roles[] = [
            'uuid' => $rola->uuid,
            'name' => $rola->name,
          ];
        }
      }
    }

    // HR: Spremi korisničke podatke i role u sesiju / EN: Save user data and roles to session
    $_SESSION['user'] = [
      'uuid' => $korisnik->uuid,
      'ime' => $korisnik->ime,
      'prezime' => $korisnik->prezime,
      'roles' => $roles,
    ];

    // Dodaj samo ako je true
    if (!empty($korisnik->privremenaLozinka)) {
      $_SESSION['user']['privremenaLozinka'] = true;
    }

    // HR: Ako je privremena lozinka, preusmjeri na promjenu lozinke / EN: If temporary password, redirect to password change
    if (!empty($_SESSION['user']['privremenaLozinka'])) {
      // redirect na promjenu lozinke
      flash_set('success', _t('Uspješno ste prijavljeni privremenom lozinkom. Promijenite lozinku.'));
      header('Location: ' . App::urlFor('passwordChange.form'));
      exit;
    }

    flash_set('success', _t('Uspješno ste prijavljeni.'));

    // HR: Ako postoji intended_url, preusmjeri tamo (uz provjeru prava) / EN: If intended_url exists, redirect there (with role check)
    if (isset($_SESSION['intended_url'])) {
      $redirectUrl = $_SESSION['intended_url'];
      $intendedMiddleware = $_SESSION['intended_middleware'] ?? null;
      unset($_SESSION['intended_url'], $_SESSION['intended_middleware']);

      if ($intendedMiddleware === 'admin') {
        // Provjeri postoji li rola s imenom "Admin" u korisničkim rolama
        $hasAdmin = false;
        foreach ($_SESSION['user']['roles'] as $role) {
          if (isset($role['name']) && $role['name'] === 'Admin') {
            $hasAdmin = true;
            break;
          }
        }
        if (!$hasAdmin) {
          flash_set('error', _t('Nemate dozvolu za pristup željenoj stranici.'));
          $redirectUrl = App::urlFor('admin.forbidden');
        }
      }
    } else {
      $redirectUrl = App::urlFor('index');
    }
    header('Location: ' . $redirectUrl);
    exit;
  }

  /**
   * HR: Odjava korisnika, uništava sesiju i CSRF token.
   * EN: Logs out user, destroys session and CSRF token.
   *
   * @return void
   */
  public function logoutPOST(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    $_SESSION = [];
    session_destroy();
    Csrf::reset();
    flash_set('success', _t('Uspješno ste odjavljeni.'));
    header('Location: ' . App::urlFor('index'));
    exit;
  }

  /**
   * HR: Prikazuje formu za zaboravljenu lozinku.
   * EN: Renders the forgot password form.
   *
   * @return void
   */
  public function zaboravljenaLozinka(): void
  {
    $this->render('korisnik/zaboravljenaLozinka', ['title' => _t('Zaboravljena lozinka')]);
  }

  /**
   * HR: Obrada zahtjeva za reset lozinke.
   * EN: Handles password reset request.
   *
   * @return void
   * @throws RuntimeException HR: Ako generiranje privremene lozinke ne uspije. / EN: If generating temporary password fails.
   * @throws Exception HR: Ako slanje e-maila ne uspije. / EN: If sending email fails.
   */
  public function zaboravljenaLozinkaPOST(): void
  {
    $unos = trim($_POST['korisnik'] ?? '');

    // HR: Pokušaj pronaći korisnika po emailu ili korisničkom imenu / EN: Try to find user by email or username
    $korisnik = null;
    $resetPoEmailu = false;
    if ($this->korisnik->existsByField('email', $unos)) {
      $korisnik = $this->korisnik->findKorisnik('email', $unos);
      $resetPoEmailu = true;
    } elseif ($this->korisnik->existsByField('korisnicko_ime', $unos)) {
      $korisnik = $this->korisnik->findKorisnik('korisnicko_ime', $unos);
    }

    // HR: Ako korisnik ne postoji, flash error i redirect / EN: If user does not exist, flash error and redirect
    if (!$korisnik) {
      flash_set('error', _t('Korisnik sa unesenim podacima nije registriran.'));
      header('Location: ' . App::urlFor('passwordReset.form'));
      exit;
    }

    try {
      // HR: Generiraj novu privremenu lozinku i spremi / EN: Generate and save new temporary password
      $novaLozinka = $this->korisnik->setTemporaryPassword($korisnik->uuid);
    } catch (RuntimeException $e) {
      flash_set('error', $e->getMessage());
      header('Location: ' . App::urlFor('passwordReset.form'));
      exit;
    }

    $config = require __DIR__ . '/../../config/mail.php';
    try {
      // HR: Slanje emaila s privremenom lozinkom / EN: Send email with temporary password
      $mailer = new Mailer($config);
      $to = $korisnik->email;
      $subject = _t('Resetiranje lozinke');
      if ($resetPoEmailu) {
        $bodyHtml = sprintf(_t("Poštovani,<br><br>Vaša nova privremena lozinka je: <strong>%s</strong><br>Korisničko ime: <strong>%s</strong><br>Molimo prijavite se i promijenite lozinku.<br><br>Lijep pozdrav."), htmlspecialchars($novaLozinka, ENT_QUOTES, 'UTF-8'), htmlspecialchars($korisnik->korisnicko_ime, ENT_QUOTES, 'UTF-8'));
        $bodyText = sprintf(_t("Poštovani,\n\nVaša nova privremena lozinka je: %s\nKorisničko ime: %s\nMolimo prijavite se i promijenite lozinku.\n\nLijep pozdrav."), $novaLozinka, $korisnik->korisnicko_ime);
      } else {
        $bodyHtml = sprintf(_t("Poštovani,<br><br>Vaša nova privremena lozinka je: <strong>%s</strong><br>Molimo prijavite se i promijenite lozinku.<br><br>Lijep pozdrav."), htmlspecialchars($novaLozinka, ENT_QUOTES, 'UTF-8'));
        $bodyText = sprintf(_t("Poštovani,\n\nVaša nova privremena lozinka je: %s\nMolimo prijavite se i promijenite lozinku.\n\nLijep pozdrav."), $novaLozinka);
      }
      $sent = $mailer->send($to, $subject, $bodyHtml, $bodyText);
    } catch (Exception) {
      flash_set('error', _t('Došlo je do greške prilikom slanja e-maila. Pokušajte ponovo.'));
      header('Location: ' . App::urlFor('passwordReset.form'));
      exit;
    }
    if ($sent) {
      flash_set('success', _t('Privremena lozinka Vam je poslana na e-mail adresu koju ste registrirali. Provjerite e-mail i prijavite se.'));
      header('Location: ' . App::urlFor('login.form'));
    } else {
      flash_set('error', _t('Slanje e-maila nije uspjelo. Pokušajte ponovo.'));
      header('Location: ' . App::urlFor('passwordReset.form'));
    }

    exit;
  }

  /**
   * HR: Prikazuje formu za promjenu lozinke.
   * EN: Renders the password change form.
   *
   * @return void
   */
  public function promjenaLozinke(): void
  {
    $this->render('korisnik/promjenaLozinke', ['title' => _t('Promjena lozinke')]);
  }

  /**
   * HR: Obrada promjene lozinke.
   * EN: Handles password change.
   *
   * @return void
   */
  public function promjenaLozinkePOST(): void
  {
    // HR: Pokreni sesiju ako nije aktivna / EN: Start session if not active
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $stara_lozinka = $_POST['lozinka'] ?? '';
    $nova_lozinka = $_POST['new_password'] ?? '';
    $potvrda_lozinke = $_POST['new_password_confirm'] ?? '';

    $errors = [];

    $uuid = $_SESSION['user']['uuid'] ?? null;
    $korisnik = $this->korisnik->findKorisnik('uuid', $uuid);

    $hash_stara = $korisnik->lozinka ?? '';

    // HR: Validacija stare i nove lozinke / EN: Validate old and new password
    if (!password_verify($stara_lozinka, $hash_stara)) {
      $errors['lozinka'] = _t('Stara lozinka nije ispravna.');
    }

    if (strlen($nova_lozinka) < 8) {
      $errors['new_password'] = _t('Nova lozinka mora imati barem 8 znakova.');
    }

    if ($nova_lozinka !== $potvrda_lozinke) {
      $errors['new_password_confirm'] = _t('Lozinke se ne podudaraju.');
    }

    if (password_verify($nova_lozinka, $hash_stara)) {
      $errors['lozinka'] = _t('Nova lozinka ne smije biti ista kao stara.');
    }

    // HR: Ako postoje greške, flash error i redirect / EN: If errors, flash error and redirect
    if (!empty($errors)) {
      flash_set('error', _t('Lozinka nije promijenjena, provjerite podatke.'));
      flash_set('errors', $errors);
      header('Location: ' . App::urlFor('passwordChange.form'));
      exit;
    }

    // HR: Spremi novu lozinku i ukloni privremenu oznaku / EN: Save new password and clear temporary flag
    $nova_lozinka_hash = password_hash($nova_lozinka, PASSWORD_DEFAULT);
    $this->korisnik->update($uuid, [
      'lozinka' => $nova_lozinka_hash,
      'privremenaLozinka' => 0,
    ]);

    // HR: Uništi sesiju i zatraži ponovni login / EN: Destroy session and require re-login
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    $_SESSION = [];
    session_destroy();
    Csrf::reset();
    flash_set('success', _t('Lozinka uspješno promijenjena. Prijavite se ponovo.'));
    header('Location: ' . App::urlFor('login.form'));
    exit;
  }

  /**
   * HR: Validacija OIB-a (Osobni identifikacijski broj).
   * EN: Validates OIB (Croatian personal identification number).
   *
   * @param string $oib HR: OIB za validaciju / EN: OIB to validate
   * @return bool HR: True ako je OIB valjan, inače false / EN: True if OIB is valid, otherwise false
   */
  public static function check(string $oib): bool
  {
    if (strlen($oib) != 11 || !ctype_digit($oib)) {
      return false;
    }
    $a = 10;
    for ($i = 0; $i < 10; $i++) {
      $a += (int)$oib[$i];
      $a %= 10;
      if ($a == 0) $a = 10;
      $a *= 2;
      $a %= 11;
    }
    $kontrolni = 11 - $a;
    if ($kontrolni == 10) $kontrolni = 0;
    return $kontrolni == (int)$oib[10];
  }
}

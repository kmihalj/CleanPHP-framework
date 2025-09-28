<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Models\Korisnik;
use App\Models\Role;
use PDO;
use PHPMailer\PHPMailer\Exception;
use RuntimeException;


class AuthController extends Controller
{

  private Role $role;
  private Korisnik $korisnik;

  public function __construct()
  {
    // Učitaj konfiguraciju baze; datoteka može vratiti PDO ili niz s 'pdo'
    $db = require __DIR__ . '/../../config/database.php';
    $pdo = ($db instanceof PDO) ? $db : ($db['pdo'] ?? null);

    if (!$pdo instanceof PDO) {
      throw new RuntimeException(_t('Neispravna DB konfiguracija: nedostaje PDO instanca'));
    }

    // konstruktori odrade migraciju i default zapise ako ih ima u modelu
    $this->role = new Role($pdo);
    $this->korisnik = new Korisnik($pdo);
  }

  public function register(): void
  { // Renderira javnu početnu stranicu s naslovom. / Renders the public home page with a title.
    $this->render('korisnik/registracija', ['title' => _t('Registracija korisnika')]);
  }

  /**
   * Obrada forme za registraciju korisnika.
   */
  public function registerPOST(): void
  {
    $errors = [];

    // Dohvati podatke iz $_POST
    $ime = trim($_POST['ime'] ?? '');
    $prezime = trim($_POST['prezime'] ?? '');
    $korisnicko_ime = trim($_POST['korisnicko_ime'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $oib = trim($_POST['oib'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';
    $potvrda_lozinke = $_POST['potvrda_lozinke'] ?? '';

    // Validacija lozinke
    if ($lozinka && strlen($lozinka) < 8) {
      $errors['lozinka'] = _t('Lozinka mora imati barem 8 znakova.');
    }
    if ($lozinka !== $potvrda_lozinke) {
      $errors['potvrda_lozinke'] = _t('Lozinke se ne podudaraju.');
      $errors['lozinka'] = _t('Lozinke se ne podudaraju.');
    }

    // Validacija OIB-a
    if ($oib && !self::check($oib)) {
      $errors['oib'] = _t('OIB nije valjan.');
    }

    // Provjera jedinstvenosti korisničkog imena, OIB-a i emaila
    if ($korisnicko_ime && $this->korisnik->existsByField('korisnicko_ime', $korisnicko_ime)) {
      $errors['korisnicko_ime'] = _t('Korisničko ime je već zauzeto.');
    }
    if ($oib && $this->korisnik->existsByField('oib', $oib)) {
      $errors['oib'] = _t('OIB je već registriran.');
    }
    if ($email && $this->korisnik->existsByField('email', $email)) {
      $errors['email'] = _t('E-mail je već registriran.');
    }

    if (!empty($errors)) {
      flash_set('error', _t('Korisnik nije kreiran, provjerite podatke.'));
      flash_set('errors', $errors);
      flash_set('old_input', [
        'ime' => $ime,
        'prezime' => $prezime,
        'korisnicko_ime' => $korisnicko_ime,
        'email' => $email,
        'oib' => $oib,
      ]);
      header('Location: ' . App::urlFor('register.form'));
      exit;
    }

    // Odredi rolu: prvi korisnik postaje Admin, ostali Registriran
    $brojKorisnika = $this->korisnik->countAll();
    $rola = ($brojKorisnika === 0) ? 'Admin' : 'Registriran';
    $role = $this->role->findByField('name', $rola);
    $role_uuid = $role['uuid'] ?? null;

    // Spremi korisnika u bazu (lozinka će biti hashirana u modelu)
    $this->korisnik->create([
      'ime' => $ime,
      'prezime' => $prezime,
      'korisnicko_ime' => $korisnicko_ime,
      'email' => $email,
      'oib' => $oib,
      'lozinka' => password_hash($lozinka, PASSWORD_DEFAULT),
      'role_uuid' => $role_uuid,
    ]);

    flash_set('success', _t('Korisnik uspješno kreiran. Možete se prijaviti'));
    header('Location: ' . App::urlFor('login.form'));
    exit;
  }

  public function login(): void
  {
    $this->render('korisnik/prijava', ['title' => _t('Prijava korisnika')]);
  }

  public function loginPOST(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $korisnicko_ime = trim($_POST['korisnicko_ime'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';

    $korisnik = $this->korisnik->findByField('korisnicko_ime', $korisnicko_ime);
    $hash = (string)($korisnik['lozinka'] ?? '');

    if (!$korisnik || !password_verify($lozinka, $hash)) {
      flash_set('error', _t('Prijava nije uspjela, provjerite korisničko ime i lozinku'));
      header('Location: ' . App::urlFor('login.form'));
      exit;
    }

    $_SESSION['user'] = [
      'uuid' => $korisnik['uuid'],
      'ime' => $korisnik['ime'],
      'prezime' => $korisnik['prezime'],
      'role_uuid' => $korisnik['role_uuid'],
      'role_name' => $this->role->findByField('uuid', $korisnik['role_uuid'])['name'] ?? null,
    ];

    // Dodaj samo ako je true
    if (!empty($korisnik['privremenaLozinka'])) {
      $_SESSION['user']['privremenaLozinka'] = true;
    }

    if (!empty($_SESSION['user']['privremenaLozinka'])) {
      // redirect na promjenu lozinke
      flash_set('success', _t('Uspješno ste prijavljeni privremenom lozinkom. Promijenite lozinku.'));
      header('Location: ' . App::urlFor('passwordChange.form'));
      exit;
    }

    flash_set('success', _t('Uspješno ste prijavljeni.'));
    if (isset($_SESSION['intended_url'])) {
      $redirectUrl = App::url($_SESSION['intended_url']);
      unset($_SESSION['intended_url']);
    } else {
      $redirectUrl = App::urlFor('index');
    }
    header('Location: ' . $redirectUrl);
    exit;
  }

  /**
   * Odjava korisnika - uništava sesiju, CSRF i preusmjerava na naslovnicu.
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

  public function zaboravljenaLozinka(): void
  {
    $this->render('korisnik/zaboravljenaLozinka', ['title' => _t('Zaboravljena lozinka')]);
  }

  /**
   * Obrada zahtjeva za reset lozinke.
   * Korisnik unosi korisničko ime ili e-mail adresu.
   */
  public function zaboravljenaLozinkaPOST(): void
  {
    $unos = trim($_POST['korisnik'] ?? '');

    // Pokušaj pronaći korisnika po emailu ili korisničkom imenu
    $korisnik = null;
    $resetPoEmailu = false;
    if ($this->korisnik->existsByField('email', $unos)) {
      $korisnik = $this->korisnik->findByField('email', $unos);
      $resetPoEmailu = true;
    } elseif ($this->korisnik->existsByField('korisnicko_ime', $unos)) {
      $korisnik = $this->korisnik->findByField('korisnicko_ime', $unos);
    }

    if (!$korisnik) {
      flash_set('error', _t('Korisnik sa unesenim podacima nije registriran.'));
      header('Location: ' . App::urlFor('passwordReset.form'));
      exit;
    }

    try {
      // Generiraj novu privremenu lozinku i spremi ju u bazu
      $novaLozinka = $this->korisnik->setTemporaryPassword($korisnik['uuid']);
    } catch (RuntimeException $e) {
      flash_set('error', $e->getMessage());
      header('Location: ' . App::urlFor('passwordReset.form'));
      exit;
    }

    $config = require __DIR__ . '/../../config/mail.php';
    try {
      $mailer = new Mailer($config);
      $to = $korisnik['email'];
      $subject = _t('Resetiranje lozinke');
      if ($resetPoEmailu) {
        $bodyHtml = sprintf(_t("Poštovani,<br><br>Vaša nova privremena lozinka je: <strong>%s</strong><br>Korisničko ime: <strong>%s</strong><br>Molimo prijavite se i promijenite lozinku.<br><br>Lijep pozdrav."), htmlspecialchars($novaLozinka, ENT_QUOTES, 'UTF-8'), htmlspecialchars($korisnik['korisnicko_ime'], ENT_QUOTES, 'UTF-8'));
        $bodyText = sprintf(_t("Poštovani,\n\nVaša nova privremena lozinka je: %s\nKorisničko ime: %s\nMolimo prijavite se i promijenite lozinku.\n\nLijep pozdrav."), $novaLozinka, $korisnik['korisnicko_ime']);
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

  public function promjenaLozinke(): void
  {
    $this->render('korisnik/promjenaLozinke', ['title' => _t('Promjena lozinke')]);
  }

  public function promjenaLozinkePOST(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $stara_lozinka = $_POST['lozinka'] ?? '';
    $nova_lozinka = $_POST['new_password'] ?? '';
    $potvrda_lozinke = $_POST['new_password_confirm'] ?? '';

    $errors = [];

    $uuid = $_SESSION['user']['uuid'] ?? null;
    $korisnik = $this->korisnik->findByField('uuid', $uuid);

    $hash_stara = (string)($korisnik['lozinka'] ?? '');

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

    if (!empty($errors)) {
      flash_set('error', _t('Lozinka nije promijenjena, provjerite podatke.'));
      flash_set('errors', $errors);
      header('Location: ' . App::urlFor('passwordChange.form'));
      exit;
    }

    $nova_lozinka_hash = password_hash($nova_lozinka, PASSWORD_DEFAULT);
    $this->korisnik->update($uuid, [
      'lozinka' => $nova_lozinka_hash,
      'privremenaLozinka' => 0,
    ]);

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
   * Validacija OIB-a (https://markoivancic.from.hr/provjera-ispravnosti-oiba-u-php-u).
   * @param string $oib
   * @return bool
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

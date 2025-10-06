<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Models\Rola;
use PDO;

class RolaController extends Controller
{
  protected PDO $pdo;
  public function __construct()
  {
    $db = require __DIR__ . '/../../config/database.php';
    $this->pdo = $db['pdo'];
  }

  public function index(): void
  {
    // HR: Renderira view 'admin/editRole' i prosljeđuje naslov
    // EN: Renders the 'admin/editRole' view and passes the title
    $dir = $_GET['dir'] ?? 'asc';
    $roleModel = new Rola($this->pdo);
    $roles = $roleModel->findAllRola($dir);
    $this->render('admin/editRole', ['title' => _t('Upravljanje ulogama'), 'roles' => $roles, 'dir' => $dir]);
  }
    /**
     * HR: Dodaje novu rolu ako ne postoji duplikat, inače postavlja grešku i vraća na index.
     * EN: Adds a new role if no duplicate exists, otherwise sets error and redirects to index.
     */
    public function create(): void
    {
        // HR: Provjera je li zahtjev POST; ako nije, preusmjeri na index
        // EN: Check if request is POST; if not, redirect to index
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $dir = $_POST['dir'] ?? 'asc';
            header('Location: ' . App::urlFor('admin.roles') . '?dir=' . urlencode($dir));
            exit;
        }

        // HR: Dohvati ime role iz POST podataka
        // EN: Retrieve role name from POST data
        $roleName = trim($_POST['roleName'] ?? '');
        $errors = [];

        // HR: Provjeri postoji li već rola s istim imenom
        // EN: Check if a role with the same name already exists
        $roleModel = new Rola($this->pdo);
        $existing = $roleModel->findByField('name', $roleName);

        if (strcasecmp($roleName, 'Gost') === 0 || $existing) {
            $errors['roleName'] = _t('Rola s istim nazivom već postoji.');
            flash_set('errors', $errors);
            flash_set('old_input', $_POST);
            $dir = $_POST['dir'] ?? ($_GET['dir'] ?? 'asc');
            header('Location: ' . App::urlFor('admin.roles') . '?dir=' . urlencode($dir));
            exit;
        }

        // HR: Pokušaj umetnuti novu rolu u bazu
        // EN: Attempt to insert new role into the database
        $success = $roleModel->create(['name' => $roleName]);
        if ($success) {
            flash_set('success', _t("Rola uspješno dodana."));
        } else {
            flash_set('error', _t("Dodavanje role nije uspjelo."));
        }
      $dir = $_POST['dir'] ?? ($_GET['dir'] ?? 'asc');
      header('Location: ' . App::urlFor('admin.roles') . '?dir=' . urlencode($dir));
      exit;
    }

    /**
     * HR: Briše rolu ako nema korisnika s tom rolom, inače postavlja grešku i vraća na index.
     * EN: Deletes a role if no users have that role, otherwise sets error and redirects to index.
     */
    public function delete(): void
    {
        // Check if request is POST; if not, redirect to index
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $dir = $_POST['dir'] ?? ($_GET['dir'] ?? 'asc');
            header('Location: ' . App::urlFor('admin.roles') . '?dir=' . urlencode($dir));
            exit;
        }

        $dir = $_POST['dir'] ?? ($_GET['dir'] ?? 'asc');
        $uuid = $_POST['uuid'] ?? null;
        if (empty($uuid)) {
            flash_set('error', _t("Rola nije pronađena."));
            header('Location: ' . App::urlFor('admin.roles') . '?dir=' . urlencode($dir));
            exit;
        }
        $roleModel = new Rola($this->pdo);
        $role = $roleModel->findRola('uuid', $uuid);
        if (!$role) {
            flash_set('error', _t("Rola nije pronađena."));
            header('Location: ' . App::urlFor('admin.roles') . '?dir=' . urlencode($dir));
            exit;
        }
        // Count users with this role
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM korisnik_rola WHERE role_uuid = :uuid");
        $stmt->execute(['uuid' => $uuid]);
        $count = (int)$stmt->fetchColumn();
        if ($count > 0) {
            flash_set('error', sprintf(_t("Rola: %s ne može biti obrisana ! Broj korisnika koji ju koriste: %s"), $role->name, $count));
            header('Location: ' . App::urlFor('admin.roles') . '?dir=' . urlencode($dir));
            exit;
        }
        // Attempt to delete the role
        $success = $roleModel->deleteByUuid($uuid);
        if ($success) {
            flash_set('success', sprintf(_t("Rola: %s je uspješno obrisana"), $role->name));
        } else {
            flash_set('error', _t("Brisanje role nije uspjelo."));
        }
        header('Location: ' . App::urlFor('admin.roles') . '?dir=' . urlencode($dir));
        exit;
    }
}

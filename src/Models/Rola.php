<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Model Role definira tablicu 'role' u bazi podataka.
 * - Koristi UUID kao primarni ključ.
 * - Polje 'name' predstavlja jedinstveno ime role.
 * - Prilikom inicijalizacije kreira tablicu ako ne postoji.
 * - Automatski dodaje početne role: "Admin" i "Registriran".
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The Role model defines the 'role' table in the database.
 * - Uses UUID as the primary key.
 * - The 'name' field represents a unique role name.
 * - On initialization, creates the table if it does not exist.
 * - Automatically inserts default roles: "Admin" and "Registriran".
 */

// HR: Model Role nasljeđuje BaseModel / EN: Role model extends BaseModel
class Rola extends BaseModel
{
  // HR: Polje 'name' - jedinstveno ime role / EN: 'name' field - unique role name
  protected array $fields = [
    'name' => ['type' => 'VARCHAR(50)', 'unique' => true]
  ];

  /**
   * HR: Popis jedinstvenih polja za tablicu role
   * EN: List of unique fields for the role table
   */
  protected array $uniqueFields = ['uuid', 'name'];

  protected string $table = 'role';

  // HR: UUID role / EN: Role UUID
  public ?string $uuid = null;

  // HR: Ime role / EN: Role name
  public ?string $name = null;

  // HR: Datum kreiranja role / EN: Role creation date
  public ?string $created_at = null;

  /**
   * HR: Konstruktor - poziva BaseModel konstruktor i osigurava default role.
   * EN: Constructor - calls BaseModel constructor and ensures default roles.
   *
   * @param PDO $pdo HR: PDO instanca za rad s bazom / EN: PDO instance for database operations
   * @return void
   */
  public function __construct(PDO $pdo)
  {
    parent::__construct($pdo);
    $this->ensureDefaultRoles();
  }

  /**
   * HR: Osigurava da u tablici postoje osnovne role, ako je prazna ubacuje "Admin" i "Registriran".
   * EN: Ensures that default roles exist in the table, inserts "Admin" and "Registriran" if empty.
   *
   * @return void
   * @throws PDOException HR: Ako SQL upit ili umetanje ne uspije / EN: If SQL query or insert fails
   */
  private function ensureDefaultRoles(): void
  {
    $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}");
    $count = (int) $stmt->fetchColumn();
    // HR: Provjerava koliko trenutno ima zapisa u tablici / EN: Checks how many records currently exist in the table

    if ($count === 0) {
      // HR: Ako nema zapisa, ubaci početne role direktno preko PDO / EN: If no records, insert default roles directly using PDO
      $stmtIns = $this->pdo->prepare("INSERT INTO {$this->table} (name) VALUES (:name)");
      foreach (['Admin', 'Registriran'] as $roleName) {
        $stmtIns->execute(['name' => $roleName]);
        // HR: Ubacuje pojedinu rolu u tablicu / EN: Inserts a single role into the table
      }
    }
  }

  /**
   * HR: Pronalazi rolu prema zadanom polju i vrijednosti.
   * Ako je pronađena, vraća instancu Role s popunjenim svojstvima, inače null.
   * EN: Finds a role by the given field and value.
   * Returns a Role instance with populated properties if found, otherwise null.
   *
   * @param string $field HR: Naziv polja za pretraživanje / EN: Field name to search by
   * @param string $value HR: Vrijednost polja za pretraživanje / EN: Field value to search for
   * @return ?self HR: Instanca Role ili null ako nije pronađena / EN: Role instance or null if not found
   */
  public function findRola(string $field, string $value): ?self
  {
    $data = $this->findByField($field, $value);
    if ($data === null) {
      return null;
    }
    $role = new self($this->pdo);
    $role->uuid = $data['uuid'] ?? null;
    $role->name = $data['name'] ?? null;
    $role->created_at = $data['created_at'] ?? null;
    return $role;
  }

  /**
   * HR: Dohvaća sve role iz baze i vraća ih kao niz objekata Rola.
   * EN: Fetches all roles from the database and returns them as an array of Rola objects.
   *
   * @return self[] HR: Niz objekata Rola / EN: Array of Rola objects
   */
  public function findAllRola(): array
  {
    $rows = $this->findAll('name');
    $roles = [];
    foreach ($rows as $row) {
      $role = new self($this->pdo);
      $role->uuid = $row['uuid'] ?? null;
      $role->name = $row['name'] ?? null;
      $role->created_at = $row['created_at'] ?? null;
      $roles[] = $role;
    }
    return $roles;
  }
}

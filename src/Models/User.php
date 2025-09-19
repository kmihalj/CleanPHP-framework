<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Model User predstavlja tablicu `users` u bazi podataka.
 * - Definira polja: id, first_name, last_name, username, email, password, role, created_at.
 * - Nasljeđuje BaseModel i koristi PDO konekciju.
 * - Automatski sinkronizira tablicu prilikom inicijalizacije (Migration::sync).
 *
 * ===========================
 *  English
 * ===========================
 * The User model represents the `users` table in the database.
 * - Defines fields: id, first_name, last_name, username, email, password, role, created_at.
 * - Inherits from BaseModel and uses PDO connection.
 * - Automatically synchronizes the table on initialization (Migration::sync).
 */

namespace App\Models;

use InvalidArgumentException;
use PDO;

class User extends BaseModel
{
  protected string $table = 'users';
  // Definicija polja tablice i njihovih atributa. / Definition of table fields and their attributes.
  protected array $fields = [
    'id' => ['type' => 'INT', 'primary' => true, 'auto_increment' => true],
    'first_name' => ['type' => 'VARCHAR(100)'],
    'last_name' => ['type' => 'VARCHAR(100)'],
    'username' => ['type' => 'VARCHAR(50)', 'unique' => true],
    'email' => ['type' => 'VARCHAR(150)', 'unique' => true],
    'password' => ['type' => 'VARCHAR(255)'],
    'role' => ['type' => 'VARCHAR(20)', 'default' => 'Registriran'],
    'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
  ];

  // Stvara novog korisnika, hashira lozinku i sprema u bazu. / Creates a new user, hashes the password, and saves it to the database.
  public function create(array $data): bool
  {
    $sql = "INSERT INTO $this->table (first_name, last_name, username, email, password, role)
                VALUES (:first_name, :last_name, :username, :email, :password, :role)";
    $stmt = $this->pdo->prepare($sql);
    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt->bindValue(':first_name', $data['first_name']);
    $stmt->bindValue(':last_name', $data['last_name']);
    $stmt->bindValue(':username', $data['username']);
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':password', $hashed);
    $stmt->bindValue(':role', $data['role'] ?? 'Registriran');
    $stmt->execute();
    return $this->pdo->lastInsertId() !== '0';
  }

  // Dohvaća korisnika po korisničkom imenu. / Retrieves a user by username.
  public function findByUsername(string $username): ?array
  {
    $sql = "SELECT * FROM $this->table WHERE username = :u LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':u', $username);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row ?: null;
  }

  // Provjerava postoji li korisnik s određenim poljem i vrijednošću. / Checks if a user exists by given field and value.
  public function existsBy(string $column, string $value): bool
  {
    $allowed = ['username', 'email']; // Only allow checking on whitelisted columns
    if (!in_array($column, $allowed, true)) {
      throw new InvalidArgumentException("Invalid column for existsBy: " . $column);
    }
    $sql = "SELECT COUNT(*) FROM $this->table WHERE {$column} = :val";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':val', $value);
    $stmt->execute();
    return (int)$stmt->fetchColumn() > 0;
  }

  // Provjerava postoji li drugi korisnik s istim korisničkim imenom (ignorira trenutnog). / Checks if another user has the same username (ignores current one).
  public function existsOtherUserWithUsername(int $id, string $username): bool
  {
    $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = :username AND id != :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return (int)$stmt->fetchColumn() > 0;
  }

  // Provjerava postoji li drugi korisnik s istim emailom (ignorira trenutnog). / Checks if another user has the same email (ignores current one).
  public function existsOtherUserWithEmail(int $id, string $email): bool
  {
    $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email AND id != :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return (int)$stmt->fetchColumn() > 0;
  }

  // Dohvaća sve korisnike sa sortiranjem. / Retrieves all users with sorting.
  public function all(string $sort = 'id', string $dir = 'ASC'): array
  {
    $allowedSort = ['id', 'first_name', 'last_name', 'username', 'email', 'created_at', 'role'];
    if (!in_array($sort, $allowedSort, true)) {
      $sort = 'id';
    }
    $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
    $sql = "SELECT id, first_name, last_name, username, email, role, created_at
                FROM $this->table
                ORDER BY $sort $dir";
    $stmt = $this->pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  // Vraca ukupan broj korisnika u tablici. / Returns total number of users in the table.
  public function countAll(): int
  {
    $sql = "SELECT COUNT(*) FROM $this->table";
    return (int)$this->pdo->query($sql)->fetchColumn();
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Vraća ukupan broj korisnika iz tablice koristeći COUNT(*).
   *
   * ===========================
   *  English
   * ===========================
   * Returns the total number of users from the table using COUNT(*).
   *
   * @return int Broj korisnika / Number of users
   */
  public function countAllUsers(): int
  {
    $sql = "SELECT COUNT(*) FROM {$this->table}";
    return (int)$this->pdo->query($sql)->fetchColumn();
  }

  // Dohvaća korisnike s paginacijom i sortiranjem. / Retrieves users with pagination and sorting.
  public function paginate(int $limit, int $offset, string $sort = 'id', string $dir = 'ASC'): array
  {
    $allowedSort = ['id', 'first_name', 'last_name', 'username', 'email', 'created_at', 'role'];
    if (!in_array($sort, $allowedSort, true)) {
      $sort = 'id';
    }
    $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
    $sql = "SELECT id, first_name, last_name, username, email, role, created_at
                FROM $this->table
                ORDER BY $sort $dir
                LIMIT :limit OFFSET :offset";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  // Ažurira korisnika po ID-u s novim podacima. / Updates a user by ID with new data.
  public function updateUser(int $id, array $data): bool
  {
    $allowed = ['first_name', 'last_name', 'username', 'email', 'role'];
    $fields = [];
    $params = [':id' => $id];

    foreach ($data as $key => $value) {
      if (in_array($key, $allowed, true)) {
        $fields[] = "$key = :$key";
        $params[":$key"] = $value;
      }
    }

    if (empty($fields)) {
      return false; // ništa za update / nothing to update
    }

    $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($params);
  }

  // Ažurira lozinku korisnika po ID-u. / Updates the user's password by ID.
  public function updatePassword(int $id, string $hashedPassword): bool
  {
    $sql = "UPDATE {$this->table} SET password = :password WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $ok = $stmt->execute();
    if (!$ok) {
      error_log("Greška pri updatePassword za user id=$id: " . implode(' | ', $stmt->errorInfo()));
    }
    return $ok;
  }

  // Dohvaća korisnika po ID-u. / Retrieves a user by ID.
  public function find(int $id): ?array
  {
    $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Briše korisnika po ID-u iz baze podataka.
   *
   * ===========================
   *  English
   * ===========================
   * Deletes a user from the database by their ID.
   *
   * @param int $id ID korisnika / User ID
   * @return bool True ako je brisanje uspješno, inače false / True if deletion was successful, otherwise false
   */
  public function deleteUser(int $id): bool
  {
    $sql = "DELETE FROM {$this->table} WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $ok = $stmt->execute();
    if (!$ok) {
      error_log("Greška pri brisanju korisnika id=$id: " . implode(' | ', $stmt->errorInfo()));
    }
    return $ok;
  }
}

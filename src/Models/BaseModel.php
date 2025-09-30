<?php


namespace App\Models;

use App\Core\Migration;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Apstraktna klasa BaseModel služi kao osnova za sve modele.
 * - Pohranjuje PDO konekciju.
 * - Definira naziv tablice i polja koja model koristi.
 * - U konstruktoru automatski pokreće Migration::sync() kako bi sinkronizirao model s bazom.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The abstract BaseModel class serves as a foundation for all models.
 * - Stores the PDO connection.
 * - Defines the table name and the fields used by the model.
 * - In the constructor, automatically calls Migration::sync() to synchronize the model with the database.
 */
abstract class BaseModel
{
  protected PDO $pdo;
  protected string $table;
  protected array $fields = [];

  private array $baseUuid = ['uuid' => 'varchar(36) NOT NULL PRIMARY KEY'];
  private array $baseCreatedAt = ['created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'];

  /**
   * HR: Konstruktor - inicijalizira model s PDO konekcijom i pokreće migraciju.
   * EN: Constructor - initializes the model with PDO connection and runs migration.
   *
   * @param PDO $pdo HR: PDO instanca za rad s bazom / EN: PDO instance for database operations
   * @return void
   */
  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
    $this->fields = $this->baseUuid + $this->fields + $this->baseCreatedAt;
    // HR: Dodaje obavezna polja uuid i created_at u definirana polja modela
    // EN: Adds required fields uuid and created_at to the model's defined fields
    Migration::sync($this->table, $this->fields, $pdo);
  }

  /**
   * HR: Kreira novi zapis i vraća UUID tog zapisa.
   * EN: Creates a new record and returns its UUID.
   *
   * @param array $data HR: Podaci koji se umeću u tablicu / EN: Data to be inserted into the table
   * @return string HR: UUID novog zapisa / EN: UUID of the newly created record
   * @throws RuntimeException HR: Ako umetanje zapisa ne uspije ili UUID nije moguće dohvatiti / EN: If insertion fails or UUID cannot be retrieved
   */
  public function create(array $data): string
  {
    $insertData = array_filter($data, function ($key) {
      return array_key_exists($key, $this->fields) && $key !== 'uuid' && $key !== 'created_at';
    }, ARRAY_FILTER_USE_KEY);
    // HR: Filtrira samo polja koja postoje u modelu, izuzima uuid i created_at
    // EN: Filters only fields defined in the model, excluding uuid and created_at
    if (empty($insertData)) {
      throw new RuntimeException(_t("Nedostaje obavezno polje"));
    }
    $columns = array_keys($insertData);
    $placeholders = array_map(function ($col) {
      return ':' . $col;
    }, $columns);
    $sql = "INSERT INTO `{$this->table}` (" . implode(', ', array_map(function ($c) {
        return "`$c`";
      }, $columns)) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $this->pdo->prepare($sql);
    foreach ($insertData as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    if (!$stmt->execute()) {
      throw new RuntimeException(_t("Neuspješno umetanje zapisa"));
    }

    // HR: Pronađi red s istim podacima, koristi WHERE sa svim ubačenim poljima
    // EN: Find the row with the same data, using WHERE with all inserted fields
    $conditions = [];
    $params = [];
    foreach ($insertData as $key => $value) {
      $conditions[] = "`$key` = :$key";
      $params[$key] = $value;
    }
    $where = implode(' AND ', $conditions);
    $sqlSelect = "SELECT uuid FROM `{$this->table}` WHERE $where ORDER BY created_at DESC LIMIT 1";
    $stmtSelect = $this->pdo->prepare($sqlSelect);
    foreach ($params as $key => $value) {
      $stmtSelect->bindValue(':' . $key, $value);
    }
    $stmtSelect->execute();
    $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
      throw new RuntimeException(_t("Nije moguće dohvatiti UUID novog zapisa"));
    }
    return $row['uuid'];
  }

  /**
   * HR: Ažurira zapis u tablici prema UUID-u.
   * EN: Updates a record in the table by UUID.
   *
   * @param string $uuid HR: UUID zapisa koji se ažurira / EN: UUID of the record to update
   * @param array $data HR: Podaci za ažuriranje / EN: Data to update
   * @return bool HR: True ako je ažuriranje uspjelo, inače false / EN: True if update succeeded, otherwise false
   * @throws InvalidArgumentException HR: Ako nema valjanih podataka ili model ne sadrži UUID polje / EN: If no valid data or model does not contain UUID field
   */
  public function update(string $uuid, array $data): bool
  {
    if (!isset($this->fields['uuid'])) {
      throw new InvalidArgumentException(_t("Model ne sadrži UUID polje."));
      // HR: Provjera da model sadrži uuid polje / EN: Check that the model contains uuid field
    }
    // HR: Filtriraj podatke samo na postojeća polja
    // EN: Filter data only on existing fields
    $updateFields = [];
    $params = [];
    foreach ($data as $key => $value) {
      if (array_key_exists($key, $this->fields) && $key !== 'uuid') {
        $updateFields[] = "`$key` = :$key";
        $params[$key] = $value;
      }
    }
    if (empty($updateFields)) {
      throw new InvalidArgumentException(_t("Nema valjanih podataka za ažuriranje."));
    }
    $params['uuid'] = $uuid;
    $sql = "UPDATE `{$this->table}` SET " . implode(', ', $updateFields) . " WHERE `uuid` = :uuid";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($params);
  }

  /**
   * HR: Pronalazi zapis prema vrijednosti određenog polja.
   * EN: Finds a record by the value of a given field.
   *
   * @param string $field HR: Naziv polja za pretragu / EN: Field name to search by
   * @param string $value HR: Vrijednost za pretragu / EN: Value to search for
   * @return array|null HR: Zapis kao asocijativno polje ili null ako ne postoji / EN: Record as associative array or null if not found
   * @throws InvalidArgumentException HR: Ako polje ne postoji u modelu / EN: If field does not exist in the model
   */
  public function findByField(string $field, string $value): ?array
  {
    if (!array_key_exists($field, $this->fields)) {
      // HR: Ako polje ne postoji u definiciji modela, baci iznimku
      // EN: If the field is not defined in the model, throw exception
      throw new InvalidArgumentException(sprintf(_t("Nevažeće polje: %s"), $field));
    }

    $stmt = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE `$field` = :value LIMIT 1");
    $stmt->execute(['value' => $value]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
  }

  /**
   * HR: Provjerava postoji li zapis prema vrijednosti određenog polja.
   * EN: Checks if a record exists by the value of a given field.
   *
   * @param string $field HR: Naziv polja za provjeru / EN: Field name to check
   * @param string $value HR: Vrijednost za provjeru / EN: Value to check
   * @return bool HR: True ako zapis postoji, inače false / EN: True if record exists, otherwise false
   * @throws InvalidArgumentException HR: Ako polje ne postoji u modelu / EN: If field does not exist in the model
   */
  public function existsByField(string $field, string $value): bool
  {
    if (!array_key_exists($field, $this->fields)) {
      throw new InvalidArgumentException(sprintf(_t("Nevažeće polje: %s"), $field));
    }
    $sql = "SELECT 1 FROM `{$this->table}` WHERE `$field` = :value LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':value', $value);
    $stmt->execute();
    return $stmt->fetchColumn() !== false;
  }

  /**
   * HR: Broji sve zapise u tablici.
   * EN: Counts all records in the table.
   *
   * @return int HR: Ukupan broj zapisa / EN: Total number of records
   */
  public function countAll(): int
  {
    $sql = "SELECT COUNT(*) FROM `{$this->table}`";
    // HR: SQL upit za brojanje svih zapisa u tablici
    // EN: SQL query to count all records in the table
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
  }

  /**
   * HR: Briše zapis prema UUID-u i vraća obrisani zapis ili false.
   * EN: Deletes a record by UUID and returns the deleted record or false.
   *
   * @param string $uuid HR: UUID zapisa koji se briše / EN: UUID of the record to delete
   * @return array|false HR: Asocijativno polje obrisanog zapisa ili false ako ne postoji / EN: Associative array of deleted record or false if not found
   */
  public function deleteByUuid(string $uuid): array|false
  {
    $stmt = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE uuid = :uuid LIMIT 1");
    // HR: Prvo dohvaća zapis koji treba obrisati
    // EN: First fetch the record that should be deleted
    $stmt->execute(['uuid' => $uuid]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
      return false;
    }

    $deleteStmt = $this->pdo->prepare("DELETE FROM `{$this->table}` WHERE uuid = :uuid");
    $deleteStmt->execute(['uuid' => $uuid]);

    return $record;
  }
}

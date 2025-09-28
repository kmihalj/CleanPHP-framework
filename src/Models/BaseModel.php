<?php


namespace App\Models;

use App\Core\Migration;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Apstraktna klasa BaseModel služi kao osnova za sve modele.
 * - Pohranjuje PDO konekciju.
 * - Definira naziv tablice ($table) i polja ($fields) koja model koristi.
 * - U konstruktoru automatski pokreće Migration::sync() kako bi sinkronizirao model s bazom (kreiranje tablice ili dodavanje kolona).
 *
 * ===========================
 *  English
 * ===========================
 * The abstract BaseModel class serves as a foundation for all models.
 * - Stores the PDO connection.
 * - Defines the table name ($table) and the fields ($fields) used by the model.
 * - In the constructor, automatically calls Migration::sync() to synchronize the model with the database (create table or add columns).
 */
abstract class BaseModel
{
  protected PDO $pdo;
  protected string $table;
  protected array $fields = [];

  private array $baseUuid = ['uuid' => 'varchar(36) NOT NULL PRIMARY KEY'];
  private array $baseCreatedAt = ['created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'];

  // Inicijalizira model s PDO konekcijom i pokreće migraciju. / Initializes the model with PDO connection and runs migration.
  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
    $this->fields = $this->baseUuid + $this->fields + $this->baseCreatedAt;
    Migration::sync($this->table, $this->fields, $pdo);
  }

  /**
   * Kreira novi zapis i vraća UUID novog zapisa.
   * Creates a new record and returns the UUID of the new record.
   *
   * @param array $data Podaci za unos / Data to insert
   * @return string UUID novog zapisa / UUID of the new record
   * @throws RuntimeException Ako nije moguće unijeti zapis ili dobiti UUID / If unable to insert record or get UUID
   */
  public function create(array $data): string
  {
    // Filtriraj podatke samo na postojeća polja, izuzmi uuid i created_at
    $insertData = array_filter($data, function ($key) {
      return array_key_exists($key, $this->fields) && $key !== 'uuid' && $key !== 'created_at';
    }, ARRAY_FILTER_USE_KEY);
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

    // Pronađi red s istim podacima, koristi WHERE sa svim ubačenim poljima
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
   * Ažurira zapis prema UUID-u.
   * Updates a record by UUID.
   *
   * @param string $uuid UUID vrijednost / UUID value
   * @param array $data Podaci za ažuriranje / Data to update
   * @return bool True ako je ažurirano / True if updated
   */
  public function update(string $uuid, array $data): bool
  {
    if (!isset($this->fields['uuid'])) {
      throw new InvalidArgumentException(_t("Model ne sadrži UUID polje."));
    }
    // Filtriraj podatke samo na postojeća polja
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
   * Pronalazi zapis prema vrijednosti određenog polja.
   * Find a record by the value of a specific field.
   *
   * @param string $field Naziv polja / Field name
   * @param string $value Vrijednost polja / Field value
   * @return array|null Zapis ili null ako nije pronađen / Record or null if not found
   * @throws InvalidArgumentException Ako polje ne postoji u definiciji modela / If field does not exist in model definition
   */
  public function findByField(string $field, string $value): ?array
  {
    if (!array_key_exists($field, $this->fields)) {
      throw new InvalidArgumentException(sprintf(_t("Nevažeće polje: %s"), $field));
    }

    $stmt = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE `$field` = :value LIMIT 1");
    $stmt->execute(['value' => $value]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row !== false ? $row : null;
  }

  /**
   * Provjerava postoji li zapis prema vrijednosti određenog polja.
   * Checks if a record exists by a given field and value.
   *
   * @param string $field Naziv polja / Field name
   * @param string $value Vrijednost polja / Field value
   * @return bool True ako postoji / True if exists
   * @throws InvalidArgumentException Ako polje ne postoji u definiciji modela / If field does not exist in model definition
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
   * Broji sve zapise u tablici.
   * Counts all records in the table.
   *
   * @return int Broj zapisa / Number of records
   */
  public function countAll(): int
  {
    $sql = "SELECT COUNT(*) FROM `{$this->table}`";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
  }


}

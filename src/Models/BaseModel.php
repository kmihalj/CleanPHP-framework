<?php

namespace App\Models;

use App\Core\Migration;
use PDO;

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

  // Inicijalizira model s PDO konekcijom i pokreće migraciju. / Initializes the model with PDO connection and runs migration.
  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
    Migration::sync($this->table, $this->fields, $pdo);
  }
}

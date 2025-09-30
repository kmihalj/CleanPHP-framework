<?php

namespace App\Core;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Klasa Migration služi za automatsku sinkronizaciju modela s bazom podataka.
 * - sync: kreira novu tablicu ako ne postoji ili dodaje nova polja ako postojeća tablica nema definirane kolone.
 * - setCollate: postavlja tip, nullabilnost, auto_increment i zadane vrijednosti.
 * - straniKljuc: priprema definiciju stranog ključa.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The Migration class is used to automatically synchronize models with the database.
 * - sync: creates a new table if it does not exist, or adds missing columns if the table already exists.
 * - setCollate: sets type, nullability, auto_increment and default values.
 * - straniKljuc: prepares foreign key definition.
 */

use PDO;
use PDOException;

class Migration
{
  /**
   * HR: Sinkronizira model s bazom podataka - kreira tablicu ako ne postoji ili dodaje nedostajuća polja.
   * EN: Synchronizes model with the database - creates table if it does not exist or adds missing columns.
   *
   * @param string $table HR: Naziv tablice koja se sinkronizira / EN: Name of the table being synchronized
   * @param array $fields HR: Definicije polja (kolona) tablice / EN: Definitions of the table fields (columns)
   * @param PDO $pdo HR: PDO instanca za rad s bazom / EN: PDO instance for database operations
   * @return void
   * @throws PDOException HR: Ako SQL izvršavanje ne uspije / EN: If SQL execution fails
   */
  public static function sync(string $table, array $fields, PDO $pdo): void
  {
    $config = require __DIR__ . '/../../config/database.php';
    // HR: Učitaj konfiguraciju baze podataka / EN: Load database configuration

    $collation = $config['collation'] ?? 'utf8mb4_general_ci';
    // HR: Postavi zadanu kolaciju / EN: Set default collation

    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    // HR: Provjeri postoji li tablica / EN: Check if table exists
    $exists = $stmt->fetchColumn();

    if (!$exists) {
      // HR: Ako tablica ne postoji, kreiraj novu s poljima, primarnim i stranim ključevima
      // EN: If table does not exist, create new with fields, primary and foreign keys
      $cols = [];
      $pk = null;
      $foreigns = [];
      foreach ($fields as $name => $def) {
        list($type, $normalizedDef, $nullable, $auto, $default) = self::setCollate($def);
        $unique = !empty($normalizedDef['unique']) ? ' UNIQUE' : '';
        $cols[] = "`{$name}` {$type} {$nullable}{$auto}{$default}{$unique}";
        if (!empty($normalizedDef['primary'])) $pk = $name;
        if (!empty($normalizedDef['foreign'])) {
          list($foreign, $constraintName, $onDelete, $onUpdate) = self::straniKljuc($normalizedDef['foreign'], $table, $name);
          $foreigns[] = "CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$name}`) REFERENCES `{$foreign['table']}`(`{$foreign['column']}`){$onDelete}{$onUpdate}";
        }
      }
      if ($pk) $cols[] = "PRIMARY KEY (`{$pk}`)";
      if ($foreigns) {
        $cols = array_merge($cols, $foreigns);
      }
      $sql = "CREATE TABLE `{$table}` (" . implode(", ", $cols) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE={$collation};";
      $pdo->exec($sql);
      return;
    }

    // HR: Ako tablica postoji, dohvatiti postojeće stupce i dodati nedostajuće
    // EN: If table exists, fetch existing columns and add missing ones
    $colsStmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
    $existing = [];
    foreach ($colsStmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        // Some drivers return keys in different cases; be defensive.
        $colName = $c['Field'] ?? $c['field'] ?? null;
        if ($colName !== null) {
            $existing[] = $colName;
        }
    }

    foreach ($fields as $name => $originalDef) {
        if (!in_array($name, $existing, true)) {
            // Normalize column definition
            list($type, $normalizedDef, $nullable, $auto, $default) = self::setCollate($originalDef);

            // Add column first
            $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$type} {$nullable}{$auto}{$default};";
            $pdo->exec($sql);

            // Unique constraint (add a named key to avoid duplicates)
            if (!empty($normalizedDef['unique'])) {
                $idx = "uniq_{$table}_{$name}";
                $pdo->exec("ALTER TABLE `{$table}` ADD UNIQUE KEY `{$idx}` (`{$name}`);");
            }

            // Primary key (only if there isn't one already — optional safeguard)
            if (!empty($normalizedDef['primary'])) {
                $pdo->exec("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$name}`);");
            }

            // Foreign key with optional ON DELETE / ON UPDATE
            if (!empty($normalizedDef['foreign'])) {
              list($foreign, $constraintName, $onDelete, $onUpdate) = self::straniKljuc($normalizedDef['foreign'], $table, $name);
              $pdo->exec(
                    "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$name}`) REFERENCES `{$foreign['table']}`(`{$foreign['column']}`){$onDelete}{$onUpdate};"
                );
            }
        }
    }
  }

  /**
   * HR: Postavlja tip podatka, nullabilnost, auto_increment i default vrijednost za polje.
   * EN: Sets data type, nullability, auto_increment, and default value for a field.
   *
   * @param mixed $def HR: Definicija polja / EN: Field definition
   * @return array HR: Polje s normaliziranom definicijom [tip, definicija, nullabilnost, auto_increment, default]
   *                EN: Array with normalized definition [type, definition, nullability, auto_increment, default]
   */
  public static function setCollate(mixed $def): array
  {
      $type = $def['type'] ?? 'VARCHAR(255)';
      // HR: Tip podatka, default VARCHAR(255) / EN: Data type, default VARCHAR(255)

      $nullable = !empty($def['nullable']) ? 'NULL' : 'NOT NULL';
      // HR: Da li polje može biti NULL / EN: Whether field can be NULL

      $auto = !empty($def['auto_increment']) ? ' AUTO_INCREMENT' : '';

      $default = '';
      if (array_key_exists('default', $def)) {
          $d = $def['default'];
          if ($d === 'UUID()') {
              // MySQL 8 supports expression defaults; use parentheses
              $default = ' DEFAULT (UUID())';
          } elseif (is_string($d)) {
              $up = strtoupper($d);
              if ($up === 'CURRENT_TIMESTAMP') {
                  $default = ' DEFAULT CURRENT_TIMESTAMP';
              } else {
                  $default = " DEFAULT '" . addslashes($d) . "'";
              }
          } elseif (is_bool($d)) {
              $default = ' DEFAULT ' . ($d ? '1' : '0');
          } elseif ($d === null) {
              $default = ' DEFAULT NULL';
          } else {
              // numbers etc.
              $default = ' DEFAULT ' . $d;
          }
      }

      // Return normalized definition alongside SQL fragments
      return [$type, $def, $nullable, $auto, $default];
  }

  /**
   * HR: Priprema definiciju stranog ključa za tablicu.
   * EN: Prepares foreign key definition for a table.
   *
   * @param array $foreign1 HR: Definicija stranog ključa (tablica, kolona, akcije) / EN: Foreign key definition (table, column, actions)
   * @param string $table HR: Naziv trenutne tablice / EN: Name of the current table
   * @param int|string $name HR: Naziv kolone koja je strani ključ / EN: Name of the column that is a foreign key
   * @return array HR: Polje s detaljima [foreign, constraintName, onDelete, onUpdate]
   *                EN: Array with details [foreign, constraintName, onDelete, onUpdate]
   */
  public static function straniKljuc(array $foreign1, string $table, int|string $name): array
  {
    $foreign = $foreign1;
    $constraintName = "fk_{$table}_{$name}";
    // HR: Generiraj naziv ograničenja za strani ključ / EN: Generate constraint name for foreign key

    $onDelete = !empty($foreign['on_delete']) ? " ON DELETE {$foreign['on_delete']}" : '';
    // HR: Dodaj ON DELETE akciju ako postoji / EN: Add ON DELETE action if exists

    $onUpdate = !empty($foreign['on_update']) ? " ON UPDATE {$foreign['on_update']}" : '';
    return array($foreign, $constraintName, $onDelete, $onUpdate);
  }
}

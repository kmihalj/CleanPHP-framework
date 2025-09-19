<?php

namespace App\Core;

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Klasa Migration služi za automatsku sinkronizaciju modela s bazom podataka.
 * - sync: kreira novu tablicu ako ne postoji ili dodaje nova polja ako postojeća tablica nema definirane kolone.
 * - setCollate: postavlja SQL tipove i kolaciju (za tekstualna polja koristi utf8mb4_croatian_ci).
 *
 * ===========================
 *  English
 * ===========================
 * The Migration class is used to automatically synchronize models with the database.
 * - sync: creates a new table if it does not exist, or adds missing columns if the table already exists.
 * - setCollate: configures SQL types and collation (for text fields it uses utf8mb4_croatian_ci).
 */

use PDO;

class Migration
{
  // Sinkronizira model s bazom: kreira tablicu ili dodaje nova polja. / Synchronizes model with database: creates table or adds new columns.
  public static function sync(string $table, array $fields, PDO $pdo): void
  {
    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    $exists = $stmt->fetchColumn();

    if (!$exists) {
      $cols = [];
      $pk = null;
      foreach ($fields as $name => $def) {
        list($type, $def, $nullable, $auto, $collate) = self::setCollate($def);
        $unique = !empty($def['unique']) ? ' UNIQUE' : '';
        $cols[] = "`{$name}` {$type}{$collate} {$nullable}{$auto}{$unique}";
        if (!empty($def['primary'])) $pk = $name;
      }
      if ($pk) $cols[] = "PRIMARY KEY (`{$pk}`)";
      $sql = "CREATE TABLE `{$table}` (" . implode(", ", $cols) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_croatian_ci;";
      $pdo->exec($sql);
      return;
    }

    $colsStmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
    $existing = [];
    foreach ($colsStmt->fetchAll() as $c) {
      $existing[] = $c['Field'];
    }
    foreach ($fields as $name => $def) {
      if (!in_array($name, $existing, true)) {
        list($type, $def, $nullable, $auto, $collate) = self::setCollate($def);
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$type}{$collate} {$nullable}{$auto};";
        $pdo->exec($sql);
        if (!empty($def['unique'])) {
          $pdo->exec("ALTER TABLE `{$table}` ADD UNIQUE (`{$name}`);");
        }
        if (!empty($def['primary'])) {
          $pdo->exec("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$name}`);");
        }
      }
    }
  }

  // Postavlja tip podatka, nullabilnost, auto_increment i kolaciju za polje. / Sets data type, nullability, auto_increment, and collation for a field.
  public static function setCollate(mixed $def): array
  {
    $type = $def['type'] ?? 'VARCHAR(255)';
    $nullable = !empty($def['nullable']) ? 'NULL' : 'NOT NULL';
    $auto = !empty($def['auto_increment']) ? ' AUTO_INCREMENT' : '';
    $collate = (stripos($type, 'CHAR') !== false || stripos($type, 'TEXT') !== false)
      ? ' COLLATE utf8mb4_croatian_ci' : '';
    $default = '';
    if (isset($def['default'])) {
      $d = $def['default'];
      if (is_string($d) && strtoupper($d) !== 'CURRENT_TIMESTAMP') {
        $default = " DEFAULT '" . addslashes($d) . "'";
      } else {
        $default = " DEFAULT " . $d;
      }
    }
    return array($type, $def, $nullable, $auto, $collate . $default);
  }
}

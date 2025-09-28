<?php

namespace App\Core;

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Klasa Migration služi za automatsku sinkronizaciju modela s bazom podataka.
 * - sync: kreira novu tablicu ako ne postoji ili dodaje nova polja ako postojeća tablica nema definirane kolone.
 * - setCollate: postavlja tip, nullabilnost, auto_increment i zadane vrijednosti.
 *
 * ===========================
 *  English
 * ===========================
 * The Migration class is used to automatically synchronize models with the database.
 * - sync: creates a new table if it does not exist, or adds missing columns if the table already exists.
 * - setCollate: sets type, nullability, auto_increment and default values.
 */

use PDO;

class Migration
{
  // Sinkronizira model s bazom: kreira tablicu ili dodaje nova polja. / Synchronizes model with database: creates table or adds new columns.
  public static function sync(string $table, array $fields, PDO $pdo): void
  {
    $config = require __DIR__ . '/../../config/database.php';
    $collation = $config['collation'] ?? 'utf8mb4_general_ci';

    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
    $exists = $stmt->fetchColumn();

    if (!$exists) {
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

  // Postavlja tip podatka, nullabilnost, auto_increment i kolaciju za polje. / Sets data type, nullability, auto_increment, and collation for a field.
  public static function setCollate(mixed $def): array
  {
      $type = $def['type'] ?? 'VARCHAR(255)';
      $nullable = !empty($def['nullable']) ? 'NULL' : 'NOT NULL';
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
   * @param $foreign1
   * @param string $table
   * @param int|string $name
   * @return array
   */
  public static function straniKljuc($foreign1, string $table, int|string $name): array
  {
    $foreign = $foreign1;
    $constraintName = "fk_{$table}_{$name}";
    $onDelete = !empty($foreign['on_delete']) ? " ON DELETE {$foreign['on_delete']}" : '';
    $onUpdate = !empty($foreign['on_update']) ? " ON UPDATE {$foreign['on_update']}" : '';
    return array($foreign, $constraintName, $onDelete, $onUpdate);
  }
}

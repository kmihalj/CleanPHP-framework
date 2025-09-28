<?php

namespace App\Models;

use PDO;
use Random\RandomException;
use RuntimeException;

class Korisnik extends BaseModel
{
  protected string $table = 'korisnik';

  protected array $fields = [
    'ime' => ['type' => 'VARCHAR(100)'],
    'prezime' => ['type' => 'VARCHAR(100)'],
    'oib' => ['type' => 'CHAR(11)', 'unique' => true],
    'korisnicko_ime' => ['type' => 'VARCHAR(50)', 'unique' => true],
    'email' => ['type' => 'VARCHAR(150)', 'unique' => true],
    'lozinka' => ['type' => 'VARCHAR(255)'],
    'privremenaLozinka' => ['type' => 'BOOLEAN', 'default' => false],
    'role_uuid' => [
      'type' => 'CHAR(36)',
      'foreign' => [
        'table' => 'role',
        'column' => 'uuid',
        'on_delete' => 'CASCADE',
        'on_update' => 'CASCADE'
      ]
    ]
  ];


  public function __construct(PDO $pdo)
  {
    parent::__construct($pdo);
  }
  public function setTemporaryPassword(string $uuid): string
  {
      // Generiraj privremenu lozinku
      try {
          $password = bin2hex(random_bytes(4));
      } catch (RandomException) {
          throw new RuntimeException(_t("Greška pri generiranju privremene lozinke"));
      }
      $hashed = password_hash($password, PASSWORD_DEFAULT);

      // Iskoristi postojeću update() metodu
      $updated = $this->update($uuid, [
          'lozinka' => $hashed,
          'privremenaLozinka' => 1,
      ]);

      if (!$updated) {
          throw new RuntimeException(_t("Nije moguće postaviti privremenu lozinku (UUID nije pronađen)"));
      }

      return $password;
  }
}

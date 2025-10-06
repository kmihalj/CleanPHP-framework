<?php

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Model KorisnikRola predstavlja tablicu 'korisnik_rola'.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The KorisnikRola model represents the 'korisnik_rola' table.
 */

namespace App\Models;

use PDO;

// HR: Model Korisnik nasljeđuje BaseModel / EN: Korisnik model extends BaseModel
class KorisnikRola extends BaseModel
{
  /**
   * @var string|null
   * HR: Jedinstveni identifikator korisnika (UUID)
   * EN: Unique user identifier (UUID)
   */
  public ?string $uuid = null;

  /**
   * @var string|null
   * HR: Uloga korisnika (UUID strane tablice)
   * EN: User's role UUID (foreign key)
   */
  public ?string $korisnik_uuid = null;

  /**
   * @var string|null
   * HR: Uloga korisnika (UUID strane tablice)
   * EN: User's role UUID (foreign key)
   */
  public ?string $role_uuid = null;

  /**
   * @var string|null
   * HR: Datum i vrijeme kreiranja zapisa
   * EN: Record creation datetime
   */
  public ?string $created_at = null;

  protected string $table = 'korisnik_rola';
  // HR: Naziv tablice u bazi / EN: Name of the table in the database
  /**
   * HR: Popis jedinstvenih polja za tablicu korisnik
   * EN: List of unique fields for the korisnik table
   */

  protected array $fields = [
    'korisnik_uuid' => [
      'type' => 'varchar(36)',
      'foreign' => [
        'table' => 'korisnik',
        'column' => 'uuid',
        'on_delete' => 'CASCADE',
        'on_update' => 'CASCADE'
      ]
    ],
    'role_uuid' => [
      'type' => 'varchar(36)',
      'foreign' => [
        'table' => 'role',
        'column' => 'uuid',
        'on_delete' => 'CASCADE',
        'on_update' => 'CASCADE'
      ]
    ]
  ];

  /**
   * HR: Konstruktor - poziva konstruktor BaseModel i prosljeđuje PDO instancu.
   * EN: Constructor - calls BaseModel constructor and passes the PDO instance.
   *
   * @param PDO $pdo HR: PDO instanca za rad s bazom / EN: PDO instance for database operations
   * @return void
   */
  public function __construct(PDO $pdo)
  {
    parent::__construct($pdo);
  }

  /**
   * HR: Dohvati sve zapise veze korisnika–rola za danog korisnika.
   * EN: Fetch all user–role pivot records for the given user.
   *
   * @param string $korisnikUuid HR: UUID korisnika / EN: User UUID
   * @return self[] HR: Niz KorisnikRola objekata / EN: Array of KorisnikRola objects
   */
  public function findAllByKorisnikUuid(string $korisnikUuid): array
  {
    // HR: Pripremi i izvrši upit / EN: Prepare and execute query
    $sql = "SELECT uuid, korisnik_uuid, role_uuid, created_at
            FROM `{$this->table}`
            WHERE korisnik_uuid = :uuid
            ORDER BY created_at";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['uuid' => $korisnikUuid]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];

    // HR: Mapiraj retke u KorisnikRola objekte / EN: Map rows to KorisnikRola objects
    foreach ($rows as $row) {
      $kr = new self($this->pdo);
      $kr->uuid = $row['uuid'] ?? null;
      $kr->korisnik_uuid = $row['korisnik_uuid'] ?? null;
      $kr->role_uuid = $row['role_uuid'] ?? null;
      $kr->created_at = $row['created_at'] ?? null;
      $result[] = $kr;
    }

    return $result;
  }

}

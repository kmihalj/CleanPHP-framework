<?php

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Model Korisnik predstavlja tablicu 'korisnik'.
 * Definira polja, validacije, strane ključeve i metode specifične za korisnika.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The Korisnik model represents the 'korisnik' table.
 * Defines fields, validations, foreign keys, and user-specific methods.
 */

namespace App\Models;

use PDO;
use Random\RandomException;
use RuntimeException;

// HR: Model Korisnik nasljeđuje BaseModel / EN: Korisnik model extends BaseModel
class Korisnik extends BaseModel
{
    /**
     * @var string|null
     * HR: Jedinstveni identifikator korisnika (UUID)
     * EN: Unique user identifier (UUID)
     */
    public ?string $uuid = null;

    /**
     * @var string|null
     * HR: Ime korisnika
     * EN: User's first name
     */
    public ?string $ime = null;

    /**
     * @var string|null
     * HR: Prezime korisnika
     * EN: User's last name
     */
    public ?string $prezime = null;

    /**
     * @var string|null
     * HR: OIB korisnika
     * EN: User's OIB (personal identification number)
     */
    public ?string $oib = null;

    /**
     * @var string|null
     * HR: Korisničko ime
     * EN: Username
     */
    public ?string $korisnicko_ime = null;

    /**
     * @var string|null
     * HR: Email adresa korisnika
     * EN: User's email address
     */
    public ?string $email = null;

    /**
     * @var string|null
     * HR: Hashirana lozinka
     * EN: Hashed password
     */
    public ?string $lozinka = null;

    /**
     * @var bool|null
     * HR: Privremena lozinka (true/false)
     * EN: Temporary password flag (true/false)
     */
    public ?bool $privremenaLozinka = null;

    /**
     * @var string|null
     * HR: Datum i vrijeme kreiranja zapisa
     * EN: Record creation datetime
     */
    public ?string $created_at = null;

  protected string $table = 'korisnik';
  // HR: Naziv tablice u bazi / EN: Name of the table in the database
  /**
   * HR: Popis jedinstvenih polja za tablicu korisnik
   * EN: List of unique fields for the korisnik table
   */
  protected array $uniqueFields = ['uuid', 'oib', 'korisnicko_ime', 'email'];

  protected array $fields = [
    'ime' => ['type' => 'VARCHAR(100)'], // HR: Ime korisnika / EN: User's first name
    'prezime' => ['type' => 'VARCHAR(100)'], // HR: Prezime korisnika / EN: User's last name
    'oib' => ['type' => 'CHAR(11)', 'unique' => true], // HR: Jedinstveni OIB / EN: Unique identifier (OIB)
    'korisnicko_ime' => ['type' => 'VARCHAR(50)', 'unique' => true], // HR: Jedinstveno korisničko ime / EN: Unique username
    'email' => ['type' => 'VARCHAR(150)', 'unique' => true], // HR: Jedinstvena email adresa / EN: Unique email address
    'lozinka' => ['type' => 'VARCHAR(255)'], // HR: Hashirana lozinka / EN: Hashed password
    'privremenaLozinka' => ['type' => 'BOOLEAN', 'default' => false], // HR: Flag za privremenu lozinku / EN: Flag for temporary password
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
   * HR: Postavlja privremenu lozinku korisniku prema UUID-u i vraća generiranu lozinku.
   * EN: Sets a temporary password for the user by UUID and returns the generated password.
   *
   * @param string $uuid HR: UUID korisnika kojem se postavlja privremena lozinka / EN: UUID of the user to set a temporary password
   * @return string HR: Generirana čista privremena lozinka (ne-hashirana) / EN: Generated plain temporary password (non-hashed)
   * @throws RuntimeException HR: Ako generiranje ili spremanje privremene lozinke ne uspije / EN: If generating or saving the temporary password fails
   */
  public function setTemporaryPassword(string $uuid): string
  {
      try {
          $password = bin2hex(random_bytes(4));
          // HR: Generira nasumičnu lozinku od 8 znakova / EN: Generates random 8-character password
      } catch (RandomException) {
          throw new RuntimeException(_t("Greška pri generiranju privremene lozinke"));
          // HR: Ako random_bytes ne uspije, baci iznimku / EN: If random_bytes fails, throw exception
      }

      $hashed = password_hash($password, PASSWORD_DEFAULT);
      // HR: Hashira generiranu lozinku / EN: Hashes the generated password

      $updated = $this->update($uuid, [
          'lozinka' => $hashed,
          'privremenaLozinka' => 1,
      ]);
      // HR: Ažurira zapis korisnika novom lozinkom i oznakom privremene lozinke
      // EN: Updates user record with new password and temporary password flag

      if (!$updated) {
          throw new RuntimeException(_t("Nije moguće postaviti privremenu lozinku (UUID nije pronađen)"));
          // HR: Ako ažuriranje ne uspije, baci iznimku / EN: If update fails, throw exception
      }

      return $password;
      // HR: Vrati čistu (ne-hashiranu) privremenu lozinku za slanje korisniku / EN: Return plain (non-hashed) temporary password to send to user
  }

    /**
     * HR: Pronalazi korisnika prema odabranom polju i vrijednosti.
     * EN: Finds a user by the specified field and value.
     *
     * @param string $field HR: Naziv polja za pretragu / EN: Field name to search by
     * @param string $value HR: Vrijednost polja za pretragu / EN: Field value to search for
     * @return self|null HR: Vraća instancu Korisnik ako je pronađen, inače null / EN: Returns Korisnik instance if found, otherwise null
     */
    public function findKorisnik(string $field, string $value): ?self
    {
        $row = $this->findByField($field, $value);
        if (!$row) {
            return null;
        }
        $korisnik = new self($this->pdo);
        foreach ([
            'uuid', 'ime', 'prezime', 'oib', 'korisnicko_ime',
            'email', 'lozinka', 'privremenaLozinka', 'role_uuid', 'created_at'
        ] as $prop) {
            if (array_key_exists($prop, $row)) {
                $korisnik->$prop = $row[$prop];
            }
        }
        return $korisnik;
    }
}

<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Primjer konfiguracije za povezivanje na MySQL bazu podataka pomoću PDO.
 * Ovu datoteku treba kopirati kao 'database.php' i popuniti stvarnim
 * pristupnim podacima za ispravan rad aplikacije.
 * Napomena: koristi se utf8mb4_croatian_ci za pravilno sortiranje i usporedbu znakova.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Example configuration for connecting to a MySQL database using PDO.
 * This file should be copied as 'database.php' and filled with real
 * credentials for the application to work properly.
 * Note: utf8mb4_croatian_ci is used for proper sorting and comparison of characters.
 */

$dsn = 'mysql:host=localhost;dbname=your_db;charset=utf8mb4'; // HR: DSN string za spajanje na MySQL bazu / EN: DSN string for connecting to MySQL database
$user = 'your_username'; // HR: Korisničko ime za bazu / EN: Database username
$pass = 'your_password'; // HR: Lozinka za bazu / EN: Database password

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // HR: Baca iznimke na SQL greške / EN: Throws exceptions on SQL errors
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // HR: Dohvat redaka kao asocijativna polja / EN: Fetch rows as associative arrays
  PDO::ATTR_EMULATE_PREPARES => false, // HR: Koristi native prepared statements / EN: Use native prepared statements
];

$pdo = new PDO($dsn, $user, $pass, $options);
$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_croatian_ci");
$pdo->exec("SET collation_connection = 'utf8mb4_croatian_ci'");

return [
  'pdo' => $pdo,
  'collation' => 'utf8mb4_croatian_ci',
];

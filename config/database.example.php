<?php
/**************** Hrvatska verzija ****************
 * Ovaj fajl uspostavlja vezu s MySQL bazom podataka koristeći PDO.
 * Napomena: koristi se utf8mb4_croatian_ci za pravilno sortiranje i usporedbu znakova.
 * Ovaj fajl treba kopirati u 'database.php' i popuniti stvarnim pristupnim podacima
 * kako bi aplikacija mogla ispravno raditi.
 *************************************************/

/**************** English version ****************
 * This file establishes a connection to the MySQL database using PDO.
 * Note: utf8mb4_croatian_ci is used for proper character sorting and comparison.
 * This file should be copied to 'database.php' and filled with real credentials
 * for the application to work correctly.
 ************************************************/

$dsn = 'mysql:host=localhost;dbname=your_db;charset=utf8mb4';
$user = 'your_username';
$pass = 'your_password';

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Baca iznimke na SQL greške / Throws exceptions on SQL errors
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Dohvat redaka kao asocijativna polja / Fetch rows as associative arrays
  PDO::ATTR_EMULATE_PREPARES => false, // Koristi native prepared statements / Use native prepared statements
];

$pdo = new PDO($dsn, $user, $pass, $options);
$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_croatian_ci");
$pdo->exec("SET collation_connection = 'utf8mb4_croatian_ci'");

return $pdo;

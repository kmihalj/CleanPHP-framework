<?php
declare(strict_types=1);
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Ovo je glavni ulaz u aplikaciju (front controller).
 * Pokreće sesiju i učitava Composer autoloader.
 * Povezuje se na bazu podataka i učitava konfiguraciju iz `config/`.
 * Automatski detektira `basePath` (korisno za deploy u poddirektorij).
 * Inicijalizira internacionalizaciju (I18n) na temelju `app.php`.
 * Inicijalizira router i registrira middleware (npr. 'auth').
 * Učitava rute iz `routes/web.php`.
 * Sprema zadnji GET URL u sesiju za sigurno vraćanje (osim za jezične rute).
 * Na kraju poziva dispatch za obradu zahtjeva.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * This is the main entry point of the application (front controller).
 * Starts the session and loads the Composer autoloader.
 * Connects to the database and loads configuration from `config/`.
 * Automatically detects the `basePath` (useful for subdirectory deployment).
 * Initializes internationalization (I18n) based on `app.php`.
 * Initializes the router and registers middleware (e.g., 'auth').
 * Loads routes from `routes/web.php`.
 * Stores the last GET URL in session for safe redirection (except for language routes).
 * Finally calls dispatch to handle the request.
 */
// Pokretanje sesije i učitavanje Composer autoloadera.
// Start session and load Composer autoloader.
session_start();
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\App;
use App\Core\I18n;

// Učitavanje konfiguracije baze i aplikacije.
// Load database and application configuration.
$pdo = require __DIR__ . '/../config/database.php';
$config = require __DIR__ . '/../config/app.php';

// Automatski otkrivanje basePath-a (korisno kada je aplikacija u poddirektoriju).
// Automatically detect basePath (useful when app is in a subdirectory).
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$dir1 = rtrim(dirname($scriptName), '/'); // e.g. /cms/public or /cms
if (str_ends_with($dir1, '/public')) {
  $basePath = preg_replace('#/public$#', '', $dir1); // -> /cms
} else {
  $basePath = ($dir1 === '/' ? '' : $dir1);          // -> '' (root) or /cms
}

// Inicijalizacija internacionalizacije s default i podržanim jezicima iz app.php.
// Initialize internationalization with default and supported locales from app.php.
I18n::init($config['default_locale'] ?? 'hr', $config['supported_locales'] ?? ['hr', 'en']);

// Pokretanje routera s PDO konekcijom i basePath-om.
// Initialize router with PDO connection and basePath.
$router = new Router($pdo, $basePath);

// Registracija middleware-a 'auth' – provjerava je li korisnik prijavljen, inače preusmjerava na početnu stranicu.
// Register 'auth' middleware – checks if user is logged in, otherwise redirects to homepage.
$router->registerMiddleware('auth', function () {
  if (empty($_SESSION['user_id'])) {
    header('Location: ' . App::url());
    return false;
  }
  return true;
});

// Učitaj sve rute iz datoteke routes/web.php.
// Load all routes from routes/web.php file.
$routes = require __DIR__ . '/../routes/web.php';
$routes($router);

// Spremi zadnji GET URL u sesiju za sigurno vraćanje (osim jezičnih ruta).
// Store last GET URL in session for safe redirection (except language routes).
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';

if ($method === 'GET') {
  // 🛑 ne spremati language rute kao last_get
  // 🛑 do not store language routes as last_get
  if (!preg_match('#/lang/[^/]+$#', $path)) {
    $_SESSION['last_get'] = $uri;
  }
}

// Pokreni router dispatch – obrađuje trenutni zahtjev.
// Run router dispatch – handles the current request.
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

<?php
declare(strict_types=1);
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Ovo je glavni ulaz u aplikaciju (front controller).
 * Pokreće sesiju i učitava Composer autoloader.
 * Učitava konfiguraciju iz `config/`.
 * Automatski detektira `basePath` (korisno za deploy u poddirektorij).
 * Inicijalizira internacionalizaciju (I18n) na temelju `app.php`.
 * Inicijalizira router.
 * Učitava rute iz `routes/web.php`.
 * Na kraju poziva dispatch za obradu zahtjeva.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * This is the main entry point of the application (front controller).
 * Starts the session and loads the Composer autoloader.
 * Loads configuration from `config/`.
 * Automatically detects the `basePath` (useful for subdirectory deployment).
 * Initializes internationalization (I18n) based on `app.php`.
 * Initializes the router.
 * Loads routes from `routes/web.php`.
 * Finally calls dispatch to handle the request.
 */
// Pokretanje sesije i učitavanje Composer autoloadera.
// Start session and load Composer autoloader.
session_start();
require __DIR__ . '/../vendor/autoload.php';

// Učitaj globalne helpere / Load global helpers
require_once __DIR__ . '/../src/helpers.php';

use App\Core\Router;
use App\Core\I18n;
use App\Core\App;

// Učitavanje konfiguracije aplikacije.
// Load application configuration.
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

// Pokretanje routera s basePath-om.
// Initialize router with basePath.
$router = new Router($basePath);

// Register 'auth' middleware
$router->registerMiddleware('auth', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user'])) {
        flash_set('error', _t('Morate biti prijavljeni za pristup ovoj stranici.'));
        header('Location: ' . App::urlFor('login.form'));
        return false;
    }
    return true;
});

// Učitaj sve rute iz datoteke routes/web.php.
// Load all routes from routes/web.php file.
$routes = require __DIR__ . '/../routes/web.php';
$routes($router);

// Pokreni router dispatch – obrađuje trenutni zahtjev.
// Run router dispatch – handles the current request.
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

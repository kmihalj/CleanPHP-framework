<?php
declare(strict_types=1);
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Glavni ulaz u aplikaciju (front controller).
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
 * Main entry point of the application (front controller).
 * Starts the session and loads the Composer autoloader.
 * Loads configuration from `config/`.
 * Automatically detects the `basePath` (useful for subdirectory deployment).
 * Initializes internationalization (I18n) based on `app.php`.
 * Initializes the router.
 * Loads routes from `routes/web.php`.
 * Finally calls dispatch to handle the request.
 */
// HR: Pokretanje sesije i učitavanje Composer autoloadera / EN: Start session and load Composer autoloader
session_start();
require __DIR__ . '/../vendor/autoload.php';
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://cdn.jsdelivr.net; script-src 'self' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:; object-src 'none'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'; require-trusted-types-for 'script'");

// HR: Učitaj globalne helpere / EN: Load global helpers
require_once __DIR__ . '/../src/helpers.php';

use App\Core\Router;
use App\Core\I18n;
use App\Core\App;

// HR: Učitavanje konfiguracije aplikacije / EN: Load application configuration
$config = require __DIR__ . '/../config/app.php';

// HR: Automatsko otkrivanje basePath-a (korisno kada je aplikacija u poddirektoriju) / EN: Automatically detect basePath (useful when app is in a subdirectory)
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$dir1 = rtrim(dirname($scriptName), '/'); // e.g. /cms/public or /cms
if (str_ends_with($dir1, '/public')) {
  $basePath = preg_replace('#/public$#', '', $dir1); // -> /cms
} else {
  $basePath = ($dir1 === '/' ? '' : $dir1);          // -> '' (root) or /cms
}

// HR: Inicijalizacija internacionalizacije s default i podržanim jezicima iz app.php / EN: Initialize internationalization with default and supported locales from app.php
I18n::init($config['default_locale'] ?? 'hr', $config['supported_locales'] ?? ['hr', 'en']);

// HR: Pokretanje routera s basePath-om / EN: Initialize router with basePath
$router = new Router($basePath);

// HR: Registracija 'auth' middleware-a / EN: Register 'auth' middleware
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

// HR: Registracija 'admin' middleware-a / EN: Register 'admin' middleware
$router->registerMiddleware('admin', function () {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  // HR: korisnik nije u sessionu → redirect na login / EN: user not in session → redirect to login
  if (empty($_SESSION['user'])) {
    flash_set('error', _t('Morate biti prijavljeni za pristup ovoj stranici.'));
    header('Location: ' . App::urlFor('login.form'));
    return false;
  }
  // HR: korisnik je prijavljen, ali nije Admin / EN: user is logged in but not Admin
  if (($_SESSION['user']['role_name'] ?? '') !== 'Admin') {
    header('Location: ' . App::urlFor('admin.forbidden'));
    return false;
  }
  return true;
});

// HR: Učitaj sve rute iz datoteke routes/web.php / EN: Load all routes from routes/web.php file
$routes = require __DIR__ . '/../routes/web.php';
$routes($router);

// HR: Pokreni router dispatch – obrađuje trenutni zahtjev / EN: Run router dispatch – handles the current request
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

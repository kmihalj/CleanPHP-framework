<?php
/**
 * ===========================================
 *              Hrvatski / Croatian
 * ===========================================
 * Opis: Ova datoteka definira rute aplikacije.
 * Rute se mogu definirati na dva načina:
 *   1. IDE-friendly način: $router->get('/', [AuthController::class, 'loginForm']);
 *   2. Klasični string način: $router->get('/', 'AuthController@loginForm');
 * Napomena: Drugi način može uzrokovati upozorenja u IDE-u jer IDE ne vidi direktnu referencu na klasu/metodu.
 * Middleware se može dodati kao treći argument, npr. ['auth'] za zaštitu ruta.
 * Posebna ruta: /lang/{locale} omogućuje promjenu jezika aplikacije.
 *
 * Dodavanje grupa ruta: Ako se kasnije dodaju API ili admin rute, mogu se grupirati u svoje zasebne closure datoteke, npr. routes/api.php, routes/admin.php.
 * Trenutni stil s return function (Router $router) to lako podržava.
 *
 * ===========================================
 *                 English
 * ===========================================
 * Description: This file defines the application routes.
 * Routes can be defined in two ways:
 *   1. IDE-friendly way: $router->get('/', [AuthController::class, 'loginForm']);
 *   2. Classic string way: $router->get('/', 'AuthController@loginForm');
 * Note: The second way may cause IDE warnings, since the IDE does not see a direct reference to the class/method.
 * Middleware can be added as a third argument, e.g. ['auth'] for access control.
 * Special route: /lang/{locale} allows changing the application language.
 *
 * Grouping routes: If API or admin routes are added later, they can be grouped into their own closure files, e.g. routes/api.php, routes/admin.php.
 * The current return function (Router $router) style supports this easily.
 */

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\LocaleController;
use App\Core\Router;

return function (Router $router) {
  // Javne rute
  // Public routes

  // Namjerno korišten klasični string stil za demonstraciju i stvarnu upotrebu; može izazvati upozorenja u IDE-u.
  // Intentionally using classic string style for demonstration and real usage; may trigger IDE warnings.
  $router->get('/', 'AuthController@loginForm')->name('index'); // Klasični string stil rute (može izazvati upozorenja u IDE-u). / Classic string style route (may trigger IDE warnings).
  $router->get('/login', [AuthController::class, 'loginForm'])->name('login.form');
  $router->get('/register', [AuthController::class, 'loginForm'])->name('register.form');

  // Autentikacijske rute
  // Auth routes
  $router->post('/login', [AuthController::class, 'login'])->name('login');
  $router->post('/register', [AuthController::class, 'register'])->name('register');
  $router->get('/logout', [AuthController::class, 'logout'])->name('logout');

  // Zaštićena početna ruta: middleware 'auth' je registriran u index.php i osigurava pristup samo autentificiranim korisnicima.
  // Protected home route: middleware 'auth' is registered in index.php and ensures only authenticated users can access it.
  $router->get('/home', [HomeController::class, 'index'], ['auth'])->name('home');

  // Ruta za promjenu jezika: poziva LocaleController@switch za promjenu trenutnog jezika aplikacije na temelju parametra {locale}.
  // Language switch route: calls LocaleController@switch to change the current application language based on the {locale} parameter.
  $router->get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

  // Grupirane rute za administraciju
  // Grouped routes for administration
  $router->group('/admin', ['auth'], function (Router $r) {
    $r->get('/users', [AuthController::class, 'popis'])->name('admin.users'); // Primjer admin stranice korisnika / Example admin users page
    $r->post('/users/update/{id}', [AuthController::class, 'update'])->name('admin.users.update');
    $r->post('/users/reset/{id}', [AuthController::class, 'resetPassword'])->name('admin.users.reset');
    $r->post('/users/delete/{id}', [AuthController::class, 'delete'])->name('admin.users.delete');
  });

};

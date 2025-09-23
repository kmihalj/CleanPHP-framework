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

  // Početna javna ruta sada vodi na HomeController@index kao novu javnu landing stranicu.
  // The public landing page route now points to HomeController@index as the new public landing page.
  $router->get('/', [HomeController::class, 'index'])->name('index');
  $router->get('/login', [AuthController::class, 'loginForm'])->name('login.form');
  $router->get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('password.forgot.form');
  $router->post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
  $router->get('/register', [AuthController::class, 'registerForm'])->name('register.form');

  // Autentikacijske rute
  // Auth routes
  $router->post('/login', [AuthController::class, 'login'])->name('login');
  $router->post('/register', [AuthController::class, 'register'])->name('register');
  $router->get('/logout', [AuthController::class, 'logout'])->name('logout');

  // Zaštićena ruta za dashboard: middleware 'auth' osigurava pristup samo autentificiranim korisnicima.
  // Protected dashboard route: 'auth' middleware ensures only authenticated users can access it.
  $router->get('/dashboard', [HomeController::class, 'dashboard'], ['auth'])->name('dashboard');


  // Postojeća zaštićena ruta za početnu stranicu korisnika
  // Existing protected home route for authenticated users
  $router->get('/home', [HomeController::class, 'index'], ['auth'])->name('home');

  // Zaštićena ruta za prikaz forme za promjenu lozinke (samo za autentificirane korisnike)
  // Protected route to display the password change form (authenticated users only)
  $router->get('/change-password', [AuthController::class, 'changePasswordForm'], ['auth'])->name('password.change');
  $router->post('/change-password', [AuthController::class, 'changePassword'], ['auth'])->name('password.change.post');

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

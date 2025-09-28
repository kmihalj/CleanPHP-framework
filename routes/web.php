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

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\LocaleController;
use App\Controllers\TestController;
use App\Core\Router;

return function (Router $router) {

  // Početna javna ruta sada vodi na TestController@index kao novu javnu landing stranicu.
  // The public landing page route now points to TestController@index as the new public landing page.
  $router->get('/', [HomeController::class, 'index'])->name('index');

  // Registration form route
  $router->get('/register', [AuthController::class, 'register'])->name('register.form');
  $router->post('/register', [AuthController::class, 'registerPOST'])->name('register.submit');

  $router->get('/login', [AuthController::class, 'login'])->name('login.form');
  $router->post('/login', [AuthController::class, 'loginPOST'])->name('login.submit');
  $router->post('/logout', [AuthController::class, 'logoutPOST'])->name('logout.submit');

  $router->get('/passwordChange', [AuthController::class, 'promjenaLozinke'], ['auth'])->name('passwordChange.form');
  $router->post('/passwordChange', [AuthController::class, 'promjenaLozinkePOST'], ['auth'])->name('passwordChange.submit');

  $router->get('/passwordReset', [AuthController::class, 'zaboravljenaLozinka'])->name('passwordReset.form');
  $router->post('/passwordReset', [AuthController::class, 'zaboravljenaLozinkaPOST'])->name('passwordReset.submit');

  // Test rute grupirane pod /test
  // Test routes grouped under /test
  $router->group('/test', [], function (Router $router) {
      $router->get('', [TestController::class, 'index'])->name('test.index');

      $router->post('/form-test', [TestController::class, 'formTest'])->name('test.form');

      $router->post('/message-self', [TestController::class, 'messageSelf'])->name('test.message.self');
      $router->get('/message-self', [TestController::class, 'messageSelf'])->name('test.message.self.get');

      $router->post('/error-self', [TestController::class, 'errorSelf'])->name('test.error.self');
      $router->get('/error-self', [TestController::class, 'errorSelf'])->name('test.error.self.get');
  });

  // Ruta za promjenu jezika: poziva LocaleController@switch za promjenu trenutnog jezika aplikacije na temelju parametra {locale}.
  // Language switch route: calls LocaleController@switch to change the current application language based on the {locale} parameter.
  $router->get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

};

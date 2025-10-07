<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Datoteka definira rute aplikacije.
 * Rute se mogu definirati s klasnim referencama ili klasičnim string notacijama.
 * Middleware se može dodati kao treći argument (npr. ['auth'], ['admin']).
 * Posebna ruta /lang/{locale} omogućuje promjenu jezika aplikacije.
 * Rute se mogu grupirati u zasebne datoteke (npr. api.php, admin.php).
 *
 * ===========================================================
 *  English
 * ===========================================================
 * This file defines the application routes.
 * Routes can be declared using class references or classic string notations.
 * Middleware can be added as the third argument (e.g. ['auth'], ['admin']).
 * The special route /lang/{locale} allows switching the application language.
 * Routes can be grouped into separate files (e.g. api.php, admin.php).
 */

use App\Controllers\AdminController;
use App\Controllers\RolaController;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\LocaleController;
use App\Controllers\TestController;
use App\Core\Router;

return function (Router $router) {

  // HR: Početna javna ruta vodi na HomeController@index kao landing stranicu / EN: The public landing page route points to HomeController@index as the landing page
  $router->get('/', [HomeController::class, 'index'])->name('index');

  // HR: Ruta za registraciju / EN: Registration form route
  $router->get('/register', [AuthController::class, 'register'])->name('register.form');
  $router->post('/register', [AuthController::class, 'registerPOST'])->name('register.submit');

  // HR: Ruta za login i logout / EN: Login and logout routes
  $router->get('/login', [AuthController::class, 'login'])->name('login.form');
  $router->post('/login', [AuthController::class, 'loginPOST'])->name('login.submit');
  $router->post('/logout', [AuthController::class, 'logoutPOST'])->name('logout.submit');

  // HR: Rute za promjenu lozinke (autenticirani korisnik) / EN: Password change routes (authenticated user)
  $router->get('/passwordChange', [AuthController::class, 'promjenaLozinke'], ['auth'])->name('passwordChange.form');
  $router->post('/passwordChange', [AuthController::class, 'promjenaLozinkePOST'], ['auth'])->name('passwordChange.submit');

  // HR: Rute za resetiranje lozinke / EN: Password reset routes
  $router->get('/passwordReset', [AuthController::class, 'zaboravljenaLozinka'])->name('passwordReset.form');
  $router->post('/passwordReset', [AuthController::class, 'zaboravljenaLozinkaPOST'])->name('passwordReset.submit');

  // HR: Ruta za zabranjeni pristup (admin) / EN: Forbidden access route (admin)
  $router->get('/forbidden', [AdminController::class, 'forbidden'])->name('admin.forbidden');

  // HR: Admin rute / EN: Admin routes
  $router->group('/admin', ['admin'], function (Router $router) {
      $router->get('/users', [AdminController::class, 'popisKorisnika'])->name('admin.users');
      // HR: Ruta za brisanje korisnika (admin) / EN: Route for deleting a user (admin)
      $router->post('/users/delete', [AdminController::class, 'brisanjeKorisnika'])->name('admin.users.delete');
      // HR: Ruta za resetiranje lozinke korisnika (admin) / EN: Route for resetting a user's password (admin)
      $router->post('/users/reset-password', [AdminController::class, 'resetLozinkeKorisnika'])->name('admin.users.resetPassword');
      // HR: Ruta za uređivanje korisnika (admin) / EN: Route for editing a user (admin)
      $router->post('/users/edit', [AdminController::class, 'editKorisnika'])->name('admin.users.edit');
    // HR: Ruta za export korisnika u CSV (admin) / EN: Route for exporting users to CSV (admin)
    $router->get('/users/export', [AdminController::class, 'exportUsers'])->name('admin.users.export');

      // HR: Ruta za administraciju rola (admin) / EN: Route for role administration (admin)
      $router->get('/roles', [RolaController::class, 'index'])->name('admin.roles');
      // HR: Ruta za dodavanje nove role (admin) / EN: Route for creating a new role (admin)
      $router->post('/roles', [RolaController::class, 'create'])->name('admin.roles.create');
      // HR: Ruta za brisanje role (admin) / EN: Route for deleting a role (admin)
      $router->post('/roles/delete', [RolaController::class, 'delete'])->name('admin.roles.delete');
  });

  // HR: Test rute grupirane pod /test / EN: Test routes grouped under /test
  $router->group('/test', [], function (Router $router) {
      $router->get('', [TestController::class, 'index'])->name('test.index');

      $router->post('/form-test', [TestController::class, 'formTest'])->name('test.form');

      $router->post('/message-self', [TestController::class, 'messageSelf'])->name('test.message.self');
      $router->get('/message-self', [TestController::class, 'messageSelf'])->name('test.message.self.get');

      $router->post('/error-self', [TestController::class, 'errorSelf'])->name('test.error.self');
      $router->get('/error-self', [TestController::class, 'errorSelf'])->name('test.error.self.get');
  });

  // HR: Ruta za promjenu jezika pomoću LocaleController@switch / EN: Language switch route via LocaleController@switch
  $router->get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

};

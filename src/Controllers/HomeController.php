<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Kontroler za glavnu (home) stranicu nakon prijave.
 *
 * ===========================
 *  English
 * ===========================
 * Controller for the main (home) page after login.
 */

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
  public function index(): void
  { // Renderira dashboard view s naslovom i porukom dobrodošlice. / Renders the dashboard view with a title and welcome message.
    $this->render('home/dashboard', ['title' => _t('Naslovnica'), 'message' => _t('Dobrodošli! Uspješno ste prijavljeni.')]);
  }
}

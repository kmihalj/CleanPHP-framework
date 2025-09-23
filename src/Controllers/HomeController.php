<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Kontroler za javnu početnu stranicu i dashboard nakon prijave.
 *
 * ===========================
 *  English
 * ===========================
 * Controller for the public home page and dashboard after login.
 */

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
  public function index(): void
  { // Renderira javnu početnu stranicu s naslovom. / Renders the public home page with a title.
    $this->render('home/index', ['title' => _t('Početna stranica')]);
  }

  public function dashboard(): void
  { // Renderira dashboard view s naslovom i porukom dobrodošlice. / Renders the dashboard view with a title and welcome message.
    $this->render('home/dashboard', ['title' => _t('Naslovnica'), 'message' => _t('Dobrodošli! Uspješno ste prijavljeni.')]);
  }
}

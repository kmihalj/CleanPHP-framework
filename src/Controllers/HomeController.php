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
  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Prikazuje javnu početnu stranicu s naslovom.
   *
   * @return void
   *
   * ===========================
   *  English
   * ===========================
   * Renders the public home page with a title.
   *
   * @return void
   */
  public function index(): void
  { // Renderira javnu početnu stranicu s naslovom. / Renders the public home page with a title.
    $this->render('home/index', ['title' => _t('Početna stranica')]);
  }
}

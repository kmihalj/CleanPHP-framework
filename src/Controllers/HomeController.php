<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Kontroler za javnu početnu stranicu i dashboard nakon prijave.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Controller for the public home page and dashboard after login.
 */

namespace App\Controllers;

use App\Core\Controller;

// HR: Prikazuje javnu početnu stranicu s naslovom
// EN: Renders the public home page with a title
class HomeController extends Controller
{
  /**
   * HR: Prikazuje javnu početnu stranicu s naslovom.
   * EN: Displays the public home page with a title.
   *
   * @return void
   */
  public function index(): void
  {
    // HR: Renderira view 'home/index' i prosljeđuje naslov
    // EN: Renders the 'home/index' view and passes the title
    $this->render('home/index', ['title' => _t('Početna stranica')]);
  }
}

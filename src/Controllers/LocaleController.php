<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Core\I18n;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Kontroler za promjenu jezika aplikacije.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Controller for changing the application language.
 */
class LocaleController extends Controller
{

  /**
   * HR: Postavlja odabrani jezik i preusmjerava korisnika natrag na zadnju sigurnu stranicu.
   * EN: Sets the selected locale and redirects the user back to the last safe page.
   *
   * @param string $locale HR: Odabrani jezik (npr. 'hr', 'en') / EN: Selected locale (e.g. 'hr', 'en')
   * @return void
   */
  public function switch(string $locale): void
  {
    I18n::setLocale($locale);
    // HR: Postavi novi jezik / EN: Set the new locale

    $back = $_SESSION['last_get'] ?? App::url();
    // HR: Uzmi zadnji GET URL iz sesije ili početnu stranicu / EN: Get last GET URL from session or homepage as fallback

    if (!empty($_SERVER['HTTP_REFERER'])) {
      $refPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) ?? '';
      if (!preg_match('#/lang/[^/]+$#', $refPath)) {
        $back = $_SERVER['HTTP_REFERER'];
      }
    }
    // HR: Ako postoji HTTP referer i nije lang ruta, koristi njega / EN: If HTTP referer exists and is not a lang route, use it

    $backPath = parse_url($back, PHP_URL_PATH) ?? '';
    if (preg_match('#/lang/[^/]+$#', $backPath)) {
      $back = App::url();
    }
    // HR: Ako je URL još uvijek lang ruta, koristi početnu stranicu / EN: If still a lang route, fallback to homepage

    header('Location: ' . $back);
    exit;
    // HR: Preusmjeri korisnika na određeni URL / EN: Redirect the user to the chosen URL
  }
}

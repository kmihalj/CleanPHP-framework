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
 * - Metoda switch postavlja novi jezik.
 * - Nakon promjene jezika vraća korisnika na prethodnu sigurnu stranicu.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Controller for changing the application language.
 * - The switch method sets the new locale.
 * - After changing the locale, it redirects the user back to the last safe page.
 */
class LocaleController extends Controller
{

  // Postavlja odabrani jezik i preusmjerava korisnika natrag na zadnju sigurnu stranicu. / Sets the selected locale and redirects the user back to the last safe page.
  public function switch(string $locale): void
  {
    I18n::setLocale($locale);

    // Uzmi zadnji GET URL iz sesije ili početnu stranicu / Take last GET URL from session or fallback to homepage
    $back = $_SESSION['last_get'] ?? App::url();
    // Ako postoji HTTP referer i nije lang ruta, koristi njega / If HTTP referer exists and is not a lang route, use it
    if (!empty($_SERVER['HTTP_REFERER'])) {
      $refPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) ?? '';
      if (!preg_match('#/lang/[^/]+$#', $refPath)) {
        $back = $_SERVER['HTTP_REFERER'];
      }
    }
    // Ako je i dalje lang ruta, koristi početnu stranicu / If still a lang route, fallback to homepage
    $backPath = parse_url($back, PHP_URL_PATH) ?? '';
    if (preg_match('#/lang/[^/]+$#', $backPath)) {
      $back = App::url();
    }

    // Preusmjeri korisnika na određeni URL / Redirect the user to the chosen URL
    header('Location: ' . $back);
    exit;
  }
}

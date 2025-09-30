<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Datoteka helpers.php sadrži pomoćne funkcije koje se koriste u cijeloj aplikaciji.
 * Trenutno uključuje:
 * - funkciju _t za prevođenje stringova
 * - flash_set i flash_get za rad s flash porukama u sesiji
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The helpers.php file contains helper functions used across the application.
 * Currently includes:
 * - the _t function for string translation
 * - flash_set and flash_get for handling flash messages in the session
 */

use App\Core\I18n;

if (!function_exists('_t')) {
  /**
   * HR: Prevede literalni string koristeći I18n::t() i zamjenjuje parametre.
   * EN: Translates a literal string using I18n::t() and replaces parameters.
   *
   * @param string $literal HR: Literalni string za prijevod / EN: Literal string to translate
   * @param array $params HR: Parametri za zamjenu u stringu (placeholderi) / EN: Parameters to replace in the string (placeholders)
   * @return string HR: Prevedeni string / EN: Translated string
   */
  // HR: Funkcija _t prevodi stringove koristeći I18n::t()
  // EN: The _t function translates strings using I18n::t()
  function _t(string $literal, array $params = []): string
  {
    $out = I18n::t($literal);
    // HR: Prevedi literalni string / EN: Translate the literal string
    if ($params) {
      // HR: Ako postoje parametri, zamijeni placeholdere u stringu s vrijednostima
      // EN: If parameters exist, replace placeholders in the string with values
      foreach ($params as $k => $v) {
        $out = str_replace('{' . $k . '}', (string)$v, $out);
      }
    }
    return $out;
    // HR: Vrati prevedeni string / EN: Return the translated string
  }
}

if (!function_exists('flash_set')) {
  /**
   * HR: Sprema flash poruku u sesiju pod zadanim ključem.
   * EN: Stores a flash message in the session under the given key.
   *
   * @param string $key HR: Ključ pod kojim se sprema poruka / EN: Key under which the message is stored
   * @param string|array $message HR: Flash poruka (string ili array) / EN: Flash message (string or array)
   * @return void
   */
  // HR: Sprema flash poruku (string ili array) u sesiju
  // EN: Stores a flash message (string or array) in the session
  function flash_set(string $key, string|array $message): void
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
      // HR: Ako sesija nije aktivna, pokreni ju / EN: If session not active, start it
    }
    $_SESSION['flash'][$key] = $message;
    // HR: Spremi flash poruku u sesiju pod zadanim ključem / EN: Store flash message in session under given key
  }
}

if (!function_exists('flash_get')) {
  /**
   * HR: Dohvaća flash poruku iz sesije i uklanja je nakon dohvaćanja.
   * EN: Retrieves a flash message from the session and removes it after retrieval.
   *
   * @param string $key HR: Ključ flash poruke koja se dohvaća / EN: Key of the flash message to retrieve
   * @return string|array|null HR: Flash poruka ili null ako ne postoji / EN: Flash message or null if not found
   */
  // HR: Dohvaća i uklanja flash poruku iz sesije
  // EN: Retrieves and removes a flash message from the session
  function flash_get(string $key): string|array|null
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
      // HR: Ako sesija nije aktivna, pokreni ju / EN: If session not active, start it
    }
    if (isset($_SESSION['flash'][$key])) {
      $msg = $_SESSION['flash'][$key];
      unset($_SESSION['flash'][$key]);
      // HR: Vrati flash poruku i odmah ju ukloni iz sesije / EN: Return flash message and remove it from session
      return $msg;
    }
    return null;
    // HR: Ako poruka ne postoji, vrati null / EN: If message does not exist, return null
  }
}

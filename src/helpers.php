<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Ova datoteka sadrži pomoćne funkcije za aplikaciju.
 *
 * ===========================
 *  English
 * ===========================
 * This file contains helper functions for the application.
 */

use App\Core\I18n;

if (!function_exists('_t')) { // Funkcija _t: služi za prevođenje stringova koristeći I18n::t(). / The _t function: used to translate strings using I18n::t().
  function _t(string $literal, array $params = []): string
  {
    $out = I18n::t($literal);
    if ($params) {
      // Zamjenjuje sve placeholdere s vrijednostima iz $params. / Replaces all placeholders with values from $params.
      foreach ($params as $k => $v) {
        $out = str_replace('{' . $k . '}', (string)$v, $out);
      }
    }
    return $out;
  }
}

if (!function_exists('flash_set')) {
  // Postavlja flash poruku (string ili polje) u sesiju. / Sets a flash message (string or array) in the session.
  // Sada podržava i string i array vrijednosti. / Now supports both string and array values.
  function flash_set(string $key, string|array $message): void
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    $_SESSION['flash'][$key] = $message;
  }
}

if (!function_exists('flash_get')) {
  // Dohvaća i uklanja flash poruku (string ili polje) iz sesije. / Gets and removes a flash message (string or array) from the session.
  // Sada može vratiti string, array ili null. / Now may return string, array, or null.
  function flash_get(string $key): string|array|null
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    if (isset($_SESSION['flash'][$key])) {
      $msg = $_SESSION['flash'][$key];
      unset($_SESSION['flash'][$key]);
      return $msg;
    }
    return null;
  }
}

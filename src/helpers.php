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

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Flash helper funkcije omogućuju privremeno spremanje podataka u sesiju
 * (npr. poruke, greške, stare vrijednosti formi).
 * Podaci spremljeni preko flash_set dostupni su samo u sljedećem requestu,
 * nakon čega se automatski brišu kada ih dohvatimo s flash_get.
 *
 * ===========================
 *  English
 * ===========================
 * Flash helper functions provide temporary storage of data in the session
 * (e.g., messages, validation errors, old form values).
 * Data stored with flash_set are available only in the next request,
 * after which they are automatically removed when retrieved with flash_get.
 */

if (!function_exists('flash_set')) {
  function flash_set(string $key, mixed $value): void
  {
    if (!isset($_SESSION['_flash'])) {
      $_SESSION['_flash'] = [];
    }
    $_SESSION['_flash'][$key] = $value;
  }
}

if (!function_exists('flash_get')) {
  function flash_get(string $key, mixed $default = null): mixed
  {
    $val = $_SESSION['_flash'][$key] ?? $default;
    if (isset($_SESSION['_flash'][$key])) {
      unset($_SESSION['_flash'][$key]);
    }
    if (isset($_SESSION['_flash']) && count($_SESSION['_flash']) === 0) {
      unset($_SESSION['_flash']);
    }
    return $val;
  }
}

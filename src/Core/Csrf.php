<?php

namespace App\Core;

use Random\RandomException;

class Csrf
{
  /**
   * Generira i vraća CSRF token, sprema u session ako ga nema.
   * Generates and returns a CSRF token, stores it in session if not present.
   *
   * @return string
   */
  public static function token(): string
  {
    if (!isset($_SESSION['_csrf_token'])) {
      try {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
      } catch (RandomException) {
        $_SESSION['_csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
      }
    }
    return $_SESSION['_csrf_token'];
  }

  /**
   * Vraća HTML <input type="hidden"> element sa CSRF tokenom.
   * Returns an HTML <input type="hidden"> element with the CSRF token.
   *
   * @return string
   */
  public static function input(): string
  {
    $token = self::token();
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
  }

  /**
   * Provjerava je li token u POST zahtjevu valjan koristeći hash_equals.
   * Validates if the token in the POST request is valid using hash_equals.
   *
   * @return bool
   */
  public static function validate(): bool
  {
    if (!isset($_SESSION['_csrf_token']) || !isset($_POST['_csrf_token'])) {
      return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);
  }

  /**
   * Briše token iz sessiona.
   * Removes the token from the session.
   *
   * @return void
   */
  public static function reset(): void
  {
    unset($_SESSION['_csrf_token']);
  }
}

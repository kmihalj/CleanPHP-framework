<?php

namespace App\Core;

use Random\RandomException;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Klasa za zaštitu od CSRF napada (Cross-Site Request Forgery).
 * Omogućuje generiranje, provjeru i resetiranje CSRF tokena.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Class for CSRF protection (Cross-Site Request Forgery).
 * Provides generation, validation and reset of CSRF tokens.
 */
class Csrf
{
  /**
   * HR: Generira i vraća CSRF token, sprema ga u session ako ga nema.
   * EN: Generates and returns a CSRF token, stores it in session if not present.
   *
   * @return string HR: CSRF token
   */
  public static function token(): string
  {
    if (!isset($_SESSION['_csrf_token'])) {
      // HR: Ako token ne postoji u sessionu, generiraj novi / EN: If token not in session, generate a new one
      try {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        // HR: Sigurno generiranje tokena pomoću random_bytes / EN: Secure token generation using random_bytes
      } catch (RandomException) {
        $_SESSION['_csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        // HR: Fallback na openssl_random_pseudo_bytes ako random_bytes ne uspije / EN: Fallback to openssl_random_pseudo_bytes if random_bytes fails
      }
    }
    return $_SESSION['_csrf_token'];
  }

  /**
   * HR: Vraća HTML <input type="hidden"> element sa CSRF tokenom.
   * EN: Returns an HTML <input type="hidden"> element with the CSRF token.
   *
   * @return string HR: HTML input element s CSRF tokenom / EN: HTML input element with CSRF token
   */
  public static function input(): string
  {
    $token = self::token();
    // HR: Dohvati CSRF token (generira se ako ga nema) / EN: Get CSRF token (generated if not exists)
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
  }

  /**
   * HR: Provjerava je li CSRF token iz POST zahtjeva valjan.
   * EN: Validates if the CSRF token from POST request is valid.
   *
   * @return bool HR: True ako je token valjan, false inače / EN: True if token is valid, false otherwise
   */
  public static function validate(): bool
  {
    if (!isset($_SESSION['_csrf_token']) || !isset($_POST['_csrf_token'])) {
      // HR: Ako token nije postavljen u sessionu ili POST-u, validacija ne uspijeva / EN: If token not set in session or POST, validation fails
      return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $_POST['_csrf_token']);
    // HR: Uspoređuje session i POST token pomoću hash_equals radi sigurnosti / EN: Compares session and POST token using hash_equals for security
  }

  /**
   * HR: Briše CSRF token iz sessiona.
   * EN: Removes the CSRF token from the session.
   *
   * @return void
   */
  public static function reset(): void
  {
    unset($_SESSION['_csrf_token']);
    // HR: Uklanja token iz sessiona / EN: Unset token from session
  }
}

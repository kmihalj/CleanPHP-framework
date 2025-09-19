<?php

namespace App\Core;

use Random\RandomException;

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * CSRF helper klasa za zaštitu aplikacije od Cross-Site Request Forgery napada.
 * - token: generira i pohranjuje CSRF token u sesiju
 * - input: vraća skriveno polje s CSRF tokenom za HTML forme
 * - validate: provjerava je li poslani token valjan
 * - invalidate: uklanja token iz sesije
 *
 * ===========================
 *  English
 * ===========================
 * CSRF helper class to protect the application from Cross-Site Request Forgery attacks.
 * - token: generates and stores a CSRF token in the session
 * - input: returns a hidden input field with the CSRF token for HTML forms
 * - validate: checks if the submitted token is valid
 * - invalidate: removes the token from the session
 */
class Csrf
{
  const string KEY = '_csrf_token';

  // Generira CSRF token i pohranjuje ga u sesiju (fallback na OpenSSL u slučaju greške). / Generates CSRF token and stores it in session (fallback to OpenSSL if error).
  public static function token(): string
  {
    if (empty($_SESSION[self::KEY])) {
      try {
        $_SESSION[self::KEY] = bin2hex(random_bytes(32));
      } catch (RandomException $e) {
        error_log(_t('Greška pri generiranju CSRF tokena') . ': ' . $e->getMessage());
        $_SESSION[self::KEY] = bin2hex(openssl_random_pseudo_bytes(32));
      }
    }
    return $_SESSION[self::KEY];
  }

  // Vraća <input type="hidden"> s CSRF tokenom za HTML forme. / Returns a <input type="hidden"> with CSRF token for HTML forms.
  public static function input(): string
  {
    $t = self::token();
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
  }

  // Provjerava je li poslani token isti kao onaj u sesiji. / Validates if submitted token matches the one stored in session.
  public static function validate(?string $token): bool
  {
    $sess = $_SESSION[self::KEY] ?? '';
    return is_string($token) && !empty($sess) && hash_equals($sess, $token);
  }

  // Uklanja CSRF token iz sesije. / Removes the CSRF token from session.
  public static function invalidate(): void
  {
    unset($_SESSION[self::KEY]);
  }
}

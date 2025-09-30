<?php

namespace App\Core;

use RuntimeException;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Klasa za internacionalizaciju (I18n) aplikacije.
 * Upravlja jezicima, prijevodima i fallback mehanizmom.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Internationalization (I18n) class for the application.
 * Manages languages, translations and fallback mechanism.
 */
class I18n
{
  private static string $default;
  private static array $supported = [];
  private static string $current;
  private static array $translations = []; // [locale => [hr_string => localized_string]]

  /**
   * HR: Inicijalizira I18n s default jezikom i podržanim jezicima.
   * EN: Initializes I18n with default and supported locales.
   *
   * @param string $default HR: Zadani (default) jezik / EN: Default locale
   * @param array $supported HR: Popis podržanih jezika / EN: List of supported locales
   * @return void
   */
  public static function init(string $default, array $supported): void
  {
    self::$default = $default;
    self::$supported = $supported;
    $cur = $_SESSION['locale'] ?? $default;
    // HR: Trenutni jezik iz sesije ili koristi default / EN: Current locale from session or fallback to default
    self::setLocale($cur);
    // HR: Osigurava da je default jezik učitan za fallback / EN: Ensure default locale is loaded for fallback
    if (!isset(self::$translations[self::$default])) {
      self::$translations[self::$default] = self::load(self::$default);
    }
  }

  /**
   * HR: Postavlja trenutni jezik i učitava prijevode.
   * EN: Sets the current locale and loads translations.
   *
   * @param string $locale HR: Jezik koji treba postaviti / EN: Locale to set
   * @return void
   */
  public static function setLocale(string $locale): void
  {
    if (!in_array($locale, self::$supported, true)) {
      $locale = self::$default;
      // HR: Ako jezik nije podržan, koristi default / EN: If locale not supported, use default
    }
    self::$current = $locale;
    $_SESSION['locale'] = $locale;
    if (!isset(self::$translations[$locale])) {
      self::$translations[$locale] = self::load($locale);
    }
  }

  /**
   * HR: Dohvaća trenutno aktivni jezik ili vraća default ako nije postavljen.
   * EN: Gets the current active locale or returns default if not set.
   *
   * @return string HR: Trenutni ili default jezik / EN: Current or default locale
   */
  public static function getLocale(): string
  {
    return self::$current ?: self::$default;
  }

  /**
   * HR: Prevodi zadani string; ako prijevod ne postoji vraća original.
   * EN: Translates the given string; if translation does not exist returns the original.
   *
   * @param string $hrString HR: Tekst na hrvatskom (default jezik) / EN: Text in Croatian (default locale)
   * @return string HR: Prevedeni string ili original / EN: Translated string or original
   */
  public static function t(string $hrString): string
  {
    self::ensureDefaultKey($hrString);
    // HR: Osigurava da string postoji u datoteci default jezika / EN: Ensures string exists in default locale file

    $loc = self::getLocale();
    if ($loc === self::$default) {
      return $hrString;
    }
    $val = self::$translations[$loc][$hrString] ?? null;
    if (is_string($val) && $val !== '') {
      return $val;
    }
    return $hrString;
  }

  /**
   * HR: Osigurava da zadani ključ postoji u default jeziku i sprema ga ako nedostaje.
   * EN: Ensures the given key exists in default locale and saves it if missing.
   *
   * @param string $hrString HR: Tekstualni ključ koji treba osigurati / EN: Text key to ensure
   * @return void
   */
  private static function ensureDefaultKey(string $hrString): void
  {
    $def = self::$default;
    if (!isset(self::$translations[$def])) {
      self::$translations[$def] = self::load($def);
    }
    if (!array_key_exists($hrString, self::$translations[$def])) {
      self::$translations[$def][$hrString] = $hrString;
      self::save($def, self::$translations[$def]);
    }
  }

  /**
   * HR: Učitava prijevode iz datoteke određenog jezika.
   * EN: Loads translations from the given locale file.
   *
   * @param string $locale HR: Jezik za učitavanje / EN: Locale to load
   * @return array HR: Polje prijevoda [ključ => prijevod] / EN: Translation array [key => translation]
   */
  private static function load(string $locale): array
  {
    $path = __DIR__ . '/../../lang/' . $locale . '.php';
    if (is_file($path)) {
      $data = include $path;
      if (is_array($data)) return $data;
      // HR: Ako datoteka postoji i vraća polje, koristi ga / EN: If file exists and returns array, use it
    }
    return [];
  }

  /**
   * HR: Sprema prijevode u datoteku određenog jezika.
   * EN: Saves translations into the given locale file.
   *
   * @param string $locale HR: Jezik koji se sprema / EN: Locale being saved
   * @param array $data HR: Polje prijevoda [ključ => prijevod] / EN: Translation array [key => translation]
   * @return void
   * @throws RuntimeException HR: Ako spremanje u datoteku ne uspije / EN: If saving to file fails
   */
  private static function save(string $locale, array $data): void
  {
    $path = __DIR__ . '/../../lang/' . $locale . '.php';
    // atomic write
    $export = var_export($data, true);
    // HR: Exportira polje prijevoda u PHP format / EN: Export translations array to PHP format
    $php = "<?php
return " . $export . ";
";
    $tmp = $path . '.tmp';
    file_put_contents($tmp, $php, LOCK_EX);
    rename($tmp, $path);
  }
}

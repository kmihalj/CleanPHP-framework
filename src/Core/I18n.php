<?php

namespace App\Core;

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Klasa za internacionalizaciju (I18n) aplikacije.
 * - Upravlja zadanim i podržanim jezicima.
 * - Postavlja i dohvaća trenutni jezik iz sesije.
 * - Učitava prijevode iz datoteka u direktoriju /lang.
 * - Ako string nije definiran u zadanoj datoteci jezika, automatski ga dodaje.
 * - Metoda t() prevodi tekst ili vraća zadani tekst ako prijevod ne postoji.
 *
 * ===========================
 *  English
 * ===========================
 * Internationalization (I18n) class for the application.
 * - Manages default and supported languages.
 * - Sets and retrieves the current locale from session.
 * - Loads translations from files in /lang directory.
 * - If a string is not defined in the default language file, it is auto-added.
 * - The t() method translates text or returns the default text if no translation exists.
 */
class I18n
{
  private static string $default;
  private static array $supported = [];
  private static string $current;
  private static array $translations = []; // [locale => [hr_string => localized_string]]

  public static function init(string $default, array $supported): void
  {
    // Inicijalizira I18n s default i podržanim jezicima. / Initializes I18n with default and supported locales.
    self::$default = $default;
    self::$supported = $supported;
    $cur = $_SESSION['locale'] ?? $default;
    self::setLocale($cur);
    // Ensure default is loaded too for fallback
    if (!isset(self::$translations[self::$default])) {
      self::$translations[self::$default] = self::load(self::$default);
    }
  }

  public static function setLocale(string $locale): void
  {
    // Postavlja trenutni jezik, sprema ga u sesiju i učitava prijevode. / Sets the current locale, stores it in session, and loads translations.
    if (!in_array($locale, self::$supported, true)) {
      $locale = self::$default;
    }
    self::$current = $locale;
    $_SESSION['locale'] = $locale;
    if (!isset(self::$translations[$locale])) {
      self::$translations[$locale] = self::load($locale);
    }
  }

  public static function getLocale(): string
  {
    // Vraća trenutno aktivni jezik (ili default ako nije postavljen). / Returns the currently active locale (or default if not set).
    return self::$current ?: self::$default;
  }

  /**
   * Translate literal source string in default locale (default locale is the canonical source).
   * - If current locale == default: ensure the key exists in default.php (auto-add) and return default text.
   * - If current locale != default: return translated text if exists, otherwise fallback to default text;
   *   also ensure default file contains the key.
   */
  public static function t(string $hrString): string
  {
    // Prevodi zadani tekst; ako prijevod ne postoji, vraća default tekst. / Translates given text; if no translation exists, returns default text.
    // Osigurava da ključ postoji u datoteci zadanog jezika. / Ensure key exists in default locale file.
    self::ensureDefaultKey($hrString);

    $loc = self::getLocale();
    if ($loc === self::$default) {
      return $hrString;
    }
    // If translation exists in selected locale, return it; else fallback
    $val = self::$translations[$loc][$hrString] ?? null;
    if (is_string($val) && $val !== '') {
      return $val;
    }
    return $hrString;
  }

  private static function ensureDefaultKey(string $hrString): void
  {
    // Osigurava da string postoji u datoteci zadanog jezika; dodaje ga ako nedostaje. / Ensures the string exists in default locale file; adds it if missing.
    $def = self::$default;
    if (!isset(self::$translations[$def])) {
      self::$translations[$def] = self::load($def);
    }
    if (!array_key_exists($hrString, self::$translations[$def])) {
      self::$translations[$def][$hrString] = $hrString;
      self::save($def, self::$translations[$def]);
    }
  }

  private static function load(string $locale): array
  {
    // Učitava prijevode iz datoteke za zadani jezik. / Loads translations from the file for the given locale.
    $path = __DIR__ . '/../../lang/' . $locale . '.php';
    if (is_file($path)) {
      $data = include $path;
      if (is_array($data)) return $data;
    }
    return [];
  }


  private static function save(string $locale, array $data): void
  {
    // Sprema prijevode u datoteku jezika pomoću atomic write. / Saves translations to the locale file using atomic write.
    $path = __DIR__ . '/../../lang/' . $locale . '.php';
    // atomic write
    $export = var_export($data, true);
    $php = "<?php
return " . $export . ";
";
    $tmp = $path . '.tmp';
    file_put_contents($tmp, $php, LOCK_EX);
    rename($tmp, $path);
  }
}

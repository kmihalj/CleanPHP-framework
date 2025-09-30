<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Konfiguracijske postavke aplikacije.
 * Ključevi konfiguracije:
 * - default_locale: Zadani jezik aplikacije
 * - supported_locales: Podržani jezici aplikacije
 * - locales: Mapiranje jezika na locale kodove
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Application configuration settings.
 * Configuration keys:
 * - default_locale: Default application language
 * - supported_locales: Supported application languages
 * - locales: Mapping of language codes to locale identifiers
 */
return [
  'default_locale' => 'hr', // HR: Zadani jezik aplikacije / EN: Default application language
  'supported_locales' => ['hr', 'en'], // HR: Podržani jezici aplikacije / EN: Supported application languages
  'locales' => [
    'hr' => 'hr_HR', // HR: Hrvatski locale / EN: Croatian locale
    'en' => 'en_US'  // HR: Engleski locale / EN: English locale
  ],
];

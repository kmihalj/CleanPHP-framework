*******************************************************
*************** Hrvatska verzija ***************
*******************************************************

Ovaj CMS koristi jednostavan sustav za višejezičnost.

- Zadani jezik definira se u `app.php` pod ključem `default_locale`.
- Podržani jezici definiraju se u `app.php` pod ključem `supported_locales`.
- U ovom primjeru, `hr.php` je zadani jezik, a `en.php` je podržani jezik.
- Prijevodne datoteke za podržane jezike (`en.php`, itd.) mora kreirati programer – one ne nastaju automatski.
- Svi stringovi na zadanom jeziku automatski se dodaju u prijevodnu datoteku prvi put kada se pojave.
- Da bi string bio relevantan za višejezičnost, MORA biti ispisan pomoću pomoćne funkcije `_t('tekst')`.

*******************************************************
*************** English version ***************
*******************************************************

This CMS uses a simple translation system for multilingual support.

- The default language is defined in `app.php` under the `default_locale` key.
- Supported languages are defined in `app.php` under the `supported_locales` key.
- In this example, `hr.php` is the default language, and `en.php` is supported.
- Translation files for supported languages (such as `en.php`) must be created by the developer – they are not generated automatically.
- All strings in the default language are automatically added to the translation file the first time they appear.
- For a string to be included in multilingual support, it MUST be output using the helper function `_t('text')`.

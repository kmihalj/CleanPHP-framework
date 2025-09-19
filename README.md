# CleanPHP framework

## Kratki opis / Short Description

### Hrvatski

Minimalistički PHP MVC skeleton s ugrađenim auth modulom, server-side sort/paginacijom, inline uređivanjem (AJAX),
resetom lozinke (PHPMailer), brisanje s modalom (POST + redirect), i18n, CSRF zaštitom te automatskim migracijama na
temelju definicija u modelima (schema).

Cilj je imati čist i proširiv kostur bez „teških“ ovisnosti — osim PHPMailera za slanje e-mailova.

### English

A minimalist PHP MVC skeleton featuring a built-in authentication module, server-side sorting and pagination, inline editing via AJAX, password reset (PHPMailer), deletion with a modal (POST + redirect), i18n, CSRF protection, and automatic migrations based on model (schema) definitions.

The goal is to provide a clean and extensible foundation without "heavy" dependencies — except PHPMailer for sending emails.

## Ključne značajke / Key Features

### Hrvatski

- Čisti MVC (bez velikih frameworka korištenjem samo PHP-a, a u primjeru je i JavaScript radi AJAX-a)
- Automatske migracije: modeli deklarativno definiraju schema() → skeleton kreira/usklađuje tablice
- Autentikacija: registracija, prijava, odjava, hash lozinke, session-based auth
- Middleware: npr. auth za zaštitu privatnih ruta; role-based zaštita (Admin) u view-u i/ili middleware-u
- Ruter: GET/POST, parametri u putanji, imenovane rute (name()), grupe (npr. /admin), urlFor() helper
- CSRF zaštita (hidden token u formama, validacija u kontroleru)
- Validacija i flash poruke (server-side) + showMessage() (client-side)
- i18n: helper _t('string'), runtime „žetva“ ključnih stringova u primarnom jeziku; lang/hr.php, lang/en.php
- UI: Bootstrap 5 (lokalno), Bootstrap Icons ili emoji ikone
- Popis korisnika: server-side sort + paginacija, inline uređivanje preko AJAX-a, reset lozinke (modal + e-mail),
  brisanje (modal + POST + redirect) uz očuvanje sort/dir/page/per_page i fallback na prethodnu stranicu

### English

- Clean MVC (no large frameworks, using only PHP; JavaScript included in the example for AJAX)
- Automatic migrations: models declaratively define schema(); the skeleton creates/updates tables accordingly
- Authentication: registration, login, logout, password hashing, session-based authentication
- Middleware: e.g., auth for protecting private routes; role-based protection (Admin) in the view and/or middleware
- Router: GET/POST, path parameters, named routes (name()), groups (e.g. /admin), urlFor() helper
- CSRF protection (hidden token in forms, validation in controller)
- Validation and flash messages (server-side) + showMessage() (client-side)
- i18n: _t('string') helper, runtime "harvesting" of key strings in the primary language; lang/hr.php, lang/en.php
- UI: Bootstrap 5 (local), Bootstrap Icons or emoji icons
- User listing: server-side sort + pagination, inline editing via AJAX, password reset (modal + email),
  deletion (modal + POST + redirect) with preservation of sort/dir/page/per_page and fallback to the previous page

## Tehnologije / Technologies

### Hrvatski

- PHP 8.1+
- PDO (MySQL 8) — utf8mb4 i utf8mb4_croatian_ci po defaultu (podesivo)
- Bootstrap 5 (lokalne datoteke), Bootstrap Icons (po želji)
- PHPMailer (preko Composera) za SMTP mail

### English

- PHP 8.1+
- PDO (MySQL 8) — utf8mb4 and utf8mb4_croatian_ci by default (configurable)
- Bootstrap 5 (local files), Bootstrap Icons (optional)
- PHPMailer (via Composer) for SMTP email

## Struktura projekta / Project structure

### Hrvatski

```project-root/
├─ public/                 # Document root (Apache/Nginx)
│  ├─ .htaccess
│  ├─ index.php
│  ├─ css/ bootstrap.min.css, bootstrap-icons.css, …
│  └─ js/  helpers.js, popis.js, …
├─ src/
│  ├─ Core/               # App, Router, Controller, Csrf, I18n, Mailer, …
│  ├─ Controllers/        # AuthController, HomeController, LocaleController, …
│  └─ Models/             # User.php (schema, save, update, find, count, …)
├─ views/
│  ├─ layout.php
│  ├─ auth/ login.php, popis.php
│  └─ home/ index.php
├─ routes/
│  └─ web.php             # Definicije ruta (imenovane, grupe, middleware)
├─ config/
│  ├─ app.php             # locale, basePath, …
│  ├─ database.php        # DSN, user, pass (koristi .example.php za git)
│  └─ mail.php            # SMTP postavke (koristi .example.php za git)
├─ lang/
│  ├─ hr.php              # primarni jezik (runtime popunjava nove stringove)
│  └─ en.php              # prijevod (ručno)
├─ composer.json          # PSR-4 autoload, PHPMailer
└─ README.md
```

### English

```project-root/
├─ public/                 # Document root (Apache/Nginx)
│  ├─ .htaccess
│  ├─ index.php
│  ├─ css/ bootstrap.min.css, bootstrap-icons.css, …
│  └─ js/  helpers.js, popis.js, …
├─ src/
│  ├─ Core/               # App, Router, Controller, Csrf, I18n, Mailer, …
│  ├─ Controllers/        # AuthController, HomeController, LocaleController, …
│  └─ Models/             # User.php (schema, save, update, find, count, …)
├─ views/
│  ├─ layout.php
│  ├─ auth/ login.php, popis.php
│  └─ home/ index.php
├─ routes/
│  └─ web.php             # Route definitions (named, groups, middleware)
├─ config/
│  ├─ app.php             # locale, basePath, …
│  ├─ database.php        # DSN, user, pass (use .example.php for git)
│  └─ mail.php            # SMTP settings (use .example.php for git)
├─ lang/
│  ├─ hr.php              # primary language (runtime fills in new strings)
│  └─ en.php              # translation (manual)
├─ composer.json          # PSR-4 autoload, PHPMailer
└─ README.md
```

## Zahtjevi

### Hrvatski

- PHP ≥ 8.1 (PDO, mbstring)
- MySQL 8
- Composer (ako koristiš PHPMailer)
- Apache/Nginx (preporučeno) ili PHP built-in server za lokalni test

1. Klonirajte repozitorij:
   `git clone https://github.com/kmihalj/CleanPHP-framework.git`
2. Instalirajte Composer pakete:
   `composer install`
3. Konfigurirajte aplikaciju pomoću datoteka u direktoriju `/config` (`app.php`, `database.php`, `mail.php`). Kopirajte
   odgovarajuće `.example.php` datoteke (npr. `app.example.php` u `app.php`) i prilagodite ih svojim postavkama baze i
   e-mail servera.
4. Tablice baze podataka će biti automatski kreirane na prvom pokretanju aplikacije na temelju definiranih modela.
5. Pokrenite lokalni server ili postavite na produkcijski server.

### English

- PHP ≥ 8.1 (PDO, mbstring)
- MySQL 8
- Composer (if you use PHPMailer)
- Apache/Nginx (recommended) or PHP built-in server for local testing

1. Clone the repository:
   `git clone https://github.com/kmihalj/CleanPHP-framework.git`
2. Install Composer packages:
   `composer install`
3. Configure the application using the files in the `/config` directory (`app.php`, `database.php`, `mail.php`). Copy the corresponding `.example.php` files (e.g., `app.example.php` to `app.php`) and adjust them to your database and email server settings.
4. Database tables will be automatically created on the first run based on the defined models.
5. Start a local server or deploy to a production server.

## Rute / Routes

### Hrvatski

Rute su u routes/web.php

### English

Routes are defined in routes/web.php

## Modeli i automatske migracije

### Hrvatski

Model definiraj u src/Models/, npr. User.php.
Schema opisuje tablicu; skeleton pri startu:

1. kreira tablicu ako ne postoji
2. dodaje nedostajuće kolone
3. (opcionalno) dodaje indekse/unique

Napomena: trenutni migrator pokriva CREATE i ADD COLUMN. RENAME/MODIFY kolona nisu automatizirani (moguće dodati
verzionirani migrator kasnije).

### English

Define your model in src/Models/, e.g., User.php.
The schema describes the table; on startup, the skeleton:

1. creates the table if it does not exist
2. adds missing columns
3. (optionally) adds indexes/unique constraints

Note: the current migrator covers CREATE and ADD COLUMN. RENAME/MODIFY columns are not automated (a versioned migrator may be added later).

## Autentikacija i sigurnost / Authentication and security

### Hrvatski

- Hash lozinki: password_hash() i verifikacija password_verify()
- CSRF zaštita: Csrf::input() u formi + Csrf::validate() u kontroleru
- Validacija na modelu (npr. unique, required, duljina)
- XSS: u view-ovima se koristi htmlspecialchars(); dodatno se može uvesti CSP header

### English

- Password hashing: password_hash() and verification with password_verify()
- CSRF protection: Csrf::input() in the form + Csrf::validate() in the controller
- Model validation (e.g., unique, required, length)
- XSS: htmlspecialchars() is used in views; additional CSP headers can be introduced

## Internacionalizacija / Internationalization

### Hrvatski

- Svi ispisi idu preko _t('String')
- Primarni jezik (lang/hr.php) se automatski puni novim stringovima kad se pojave na ekranu
- Prijevodi se ručno održavaju u lang/en.php (ili drugim jezicima)
- Promjena jezika: /lang/{locale} (npr. hr, en)
- Dropdown u layoutu za brzi odabir

### English

- All output is handled via _t('String')
- The primary language file (lang/hr.php) is automatically populated with new strings as they appear on screen
- Translations are maintained manually in lang/en.php (or other languages)
- Change language: /lang/{locale} (e.g., hr, en)
- Dropdown in the layout for quick selection

## UI i UX

### Hrvatski

- Bootstrap 5 i Bootstrap Icons lokalno (bez CDN)
- Popis korisnika:
  - Sort i paginacija: server-side (GET parametri)
  - Inline edit: dvoklik na polje ili ✏️ → input/select → AJAX spremanje
  - Reset lozinke: 🔑 → modal → AJAX → mail korisniku (HTML + plain text)
  - Brisanje: 🗑️ → modal → POST + redirect (očuvanje sort/dir/page/per_page; fallback na prethodnu stranicu ako je
    zadnja ostala prazna)

### English

- Bootstrap 5 and Bootstrap Icons locally (no CDN)
- User list:
  - Sorting and pagination: server-side (GET parameters)
  - Inline edit: double-click on a field or ✏️ → input/select → AJAX save
  - Password reset: 🔑 → modal → AJAX → email to user (HTML + plain text)
  - Deletion: 🗑️ → modal → POST + redirect (preserves sort/dir/page/per_page; fallback to previous page if the last one is empty)

## E-mail (PHPMailer)

### Hrvatski

- Konfiguracija u config/mail.php
- Mailer wrapper klasa: slanje HTML i/ili plain text poruka
- Reset lozinke šalje novu lozinku korisniku e-mailom

### English

- Configuration in config/mail.php
- Mailer wrapper class: send HTML and/or plain text messages
- Password reset sends a new password to the user via email

## Razvojne napomene / Development notes

### Hrvatski

- PSR-4 autoload (composer.json: "App\\": "src/")
- Kôd i komentari su dvojezični (HR/EN) zbog lakšeg onboardinga i dokumentiranja
- Inline JSDoc u popis.js za bolju IDE podršku (Modal/Alert tipovi, itd.)
- Helperi: App::url(), App::urlFor(), _t(), flash_set()/flash_get(), Csrf::input()/validate()

### English

- PSR-4 autoload (composer.json: "App\\": "src/")
- Code and comments are bilingual (HR/EN) for easier onboarding and documentation
- Inline JSDoc in popis.js for better IDE support (Modal/Alert types, etc.)
- Helpers: App::url(), App::urlFor(), _t(), flash_set()/flash_get(), Csrf::input()/validate()

## TODO / Roadmap

### Hrvatski

- Route caching, fallback auto-routing
- Napredniji validacijski sloj i form error handling helperi
- Globalni XSS mitigations (CSP, X-Content-Type-Options, itd.)
- Verzionirane migracije (migrations/ direktorij)
- Jedinični testovi (PHPUnit)

### English

- Route caching, fallback auto-routing
- More advanced validation layer and form error handling helpers
- Global XSS mitigations (CSP, X-Content-Type-Options, etc.)
- Versioned migrations (migrations/ directory)
- Unit tests (PHPUnit)

## Licenca / License

### Hrvatski

Ovaj projekt je licenciran pod MIT licencom.

### English

This project is licensed under the MIT license.

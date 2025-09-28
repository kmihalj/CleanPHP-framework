# CleanPHP framework

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## Kratki opis / Short Description

### Hrvatski

Cilj je imati čist i proširiv kostur bez ikakvih ovisnosti, samo čisti PHP i HTML/CSS.

Ovaj primjer je PHP MVC skeleton s ugrađenim auth modulom, resetom lozinke (PHPMailer), i18n, CSRF zaštitom te automatskim migracijama na temelju definicija u modelima (schema).

Autentikacija uključuje registraciju, prijavu, odjavu, promjenu lozinke, zaboravljenu lozinku (reset putem e-maila), hash lozinke i session-based auth.

### English

The goal is to provide a clean and extensible skeleton without any dependencies, using only pure PHP and HTML/CSS.

This example is a PHP MVC skeleton with a built-in authentication module, password reset (PHPMailer), i18n, CSRF protection, and automatic migrations based on model (schema) definitions.

Authentication includes registration, login, logout, password change, forgotten password (reset via email), password hashing, and session-based authentication.

## Ključne značajke / Key Features

### Hrvatski

- Čisti MVC (bez velikih frameworka korištenjem samo PHP-a)
- Automatske migracije: modeli deklarativno definiraju schema() → skeleton kreira/usklađuje tablice
- Autentikacija: registracija, prijava, odjava, promjena lozinke, zaboravljena lozinka (reset putem e-maila), hash lozinke, session-based auth
- Middleware: npr. auth za zaštitu privatnih ruta; role-based zaštita (Admin) u view-u i/ili middleware-u
- Ruter: GET/POST, parametri u putanji, imenovane rute (name()), grupe (npr. /admin), urlFor() helper
- CSRF zaštita (hidden token u formama, validacija u kontroleru)
- Validacija i flash poruke (server-side)
- i18n: helper _t('string'), primarni jezik definiran u config/app.php; runtime „žetva“ ključnih stringova; lang/hr.php, lang/en.php
- UI: Bootstrap 5 (lokalno), Bootstrap Icons ili emoji ikone
- Svi modeli preko BaseModel imaju uuid kao primarni ključ i polje created_at; svi modeli nasljeđuju globalne metode create, update, findByField, existsByField, countAll

### English

- Clean MVC (no large frameworks, using only PHP)
- Automatic migrations: models declaratively define schema(); the skeleton creates/updates tables accordingly
- Authentication: registration, login, logout, password change, forgotten password (reset via email), password hashing, session-based authentication
- Middleware: e.g., auth for protecting private routes; role-based protection (Admin) in the view and/or middleware
- Router: GET/POST, path parameters, named routes (name()), groups (e.g. /admin), urlFor() helper
- CSRF protection (hidden token in forms, validation in controller)
- Validation and flash messages (server-side)
- i18n: _t('string') helper, primary language set in config/app.php; runtime harvesting of key strings; lang/hr.php, lang/en.php
- UI: Bootstrap 5 (local files), Bootstrap Icons or emoji icons
- All models via BaseModel have uuid as primary key and created_at field; all models inherit global methods create, update, findByField, existsByField, countAll

## Tehnologije / Technologies

### Hrvatski

- PHP 8.1+
- PDO (MySQL 8) — collation/charset se konfigurira u config/database.php
- Bootstrap 5 (lokalne datoteke), Bootstrap Icons (po želji)
- PHPMailer (preko Composera) za SMTP mail

### English

- PHP 8.1+
- PDO (MySQL 8) — collation/charset is configured in config/database.php
- Bootstrap 5 (local files), Bootstrap Icons (optional)
- PHPMailer (via Composer) for SMTP email

## Struktura projekta / Project structure

### Hrvatski

```project-root/
├─ public/                 # Document root (Apache/Nginx)
│  ├─ .htaccess
│  ├─ index.php
│  ├─ css/ bootstrap.min.css, bootstrap-icons.css, …
│  └─ js/
├─ src/
│  ├─ Core/               # App, Router, Controller, Csrf, I18n, Mailer, …
│  ├─ Controllers/        # AuthController, HomeController, LocaleController, …
│  └─ Models/             # User.php (schema, save, update, find, count, …)
├─ views/
│  ├─ layout.php
│  ├─ auth/ login.php, promjenaLozinke.php, registracija.php, zaboravljenaLozinka.php
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
│  └─ js/
├─ src/
│  ├─ Core/               # App, Router, Controller, Csrf, I18n, Mailer, …
│  ├─ Controllers/        # AuthController, HomeController, LocaleController, …
│  └─ Models/             # User.php (schema, save, update, find, count, …)
├─ views/
│  ├─ layout.php
│  ├─ auth/ login.php, passwordChange.php, registration.php, forgottenPassword.php
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

## Zahtjevi / Requirements

### Hrvatski

- PHP ≥ 8.1 (PDO, mbstring)
- MySQL 8
- Composer (za PHPMailer)
- Apache/Nginx (preporučeno) ili PHP built-in server za lokalni test

1. Klonirajte repozitorij:
   `git clone https://github.com/kmihalj/CleanPHP-framework.git`
2. Instalirajte Composer pakete:
   `composer install`
3. Konfigurirajte aplikaciju pomoću datoteka u direktoriju `/config` (`app.php`, `database.php`, `mail.php`). Kopirajte
   odgovarajuće `.example.php` datoteke (npr. `app.example.php` u `app.php`) i prilagodite ih svojim postavkama baze i
   e-mail servera.
4. Podesite views/layout.php za CDN Boostrap 5 ili instalirajte lokalne bootstrap biblioteke u public/css i public/js
5. Tablice baze podataka će biti automatski kreirane na prvom pokretanju aplikacije na temelju definiranih modela.
6. Pokrenite lokalni server ili postavite na produkcijski server.

### English

- PHP ≥ 8.1 (PDO, mbstring)
- MySQL 8
- Composer (for PHPMailer)
- Apache/Nginx (recommended) or PHP built-in server for local testing

1. Clone the repository:
   `git clone https://github.com/kmihalj/CleanPHP-framework.git`
2. Install Composer packages:
   `composer install`
3. Configure the application using the files in the `/config` directory (`app.php`, `database.php`, `mail.php`). Copy the corresponding `.example.php` files (e.g., `app.example.php` to `app.php`) and adjust them to your database and email server settings.
4. Configure views/layout.php to use the Bootstrap 5 CDN, or install the local Bootstrap libraries in public/css and public/js.
5. Database tables will be automatically created on the first run based on the defined models.
6. Start a local server or deploy to a production server.

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
- Primarni jezik definiran je u config/app.php
- Primarni jezik se automatski puni novim stringovima kad se pojave na ekranu
- Prijevodi se ručno održavaju u lang/en.php (ili drugim jezicima)
- Promjena jezika: /lang/{locale} (npr. hr, en)
- Dropdown u layoutu za brzi odabir

### English

- All output is handled via _t('String')
- Primary language is defined in config/app.php
- The primary language file is automatically populated with new strings as they appear on screen
- Translations are maintained manually in lang/en.php (or other languages)
- Change language: /lang/{locale} (e.g., hr, en)
- Dropdown in the layout for quick selection

## UI i UX / UI and UX

### Hrvatski

- Bootstrap 5 i Bootstrap Icons lokalno (bez CDN)
- Obrasci uključuju odvojeni login i registraciju, promjenu lozinke, zaboravljenu lozinku

### English

- Bootstrap 5 and Bootstrap Icons locally (no CDN)
- Forms include separate login and registration, password change, forgotten password

## TODO / Roadmap

### Hrvatski

- Route caching, fallback auto-routing
- Globalni XSS mitigations (CSP, X-Content-Type-Options, itd.)
- Verzionirane migracije (migrations/ direktorij)
- Jedinični testovi (PHPUnit)

### English

- Route caching, fallback auto-routing
- Global XSS mitigations (CSP, X-Content-Type-Options, etc.)
- Versioned migrations (migrations/ directory)
- Unit tests (PHPUnit)

## Licenca / License

### Hrvatski

Ovaj projekt je licenciran pod MIT licencom.

### English

This project is licensed under the MIT license.

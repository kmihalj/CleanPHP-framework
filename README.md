# Naslov projekta / Project Title

## Kratki opis / Short Description

### Hrvatski
Ovaj projekt je sveobuhvatna web aplikacija dizajnirana za upravljanje korisnicima, autentikaciju, i slanje e-mailova koristeći PHPMailer. Omogućuje internacionalizaciju, sigurnost i intuitivno korisničko sučelje.

### English
This project is a comprehensive web application designed for user management, authentication, and email sending using PHPMailer. It supports internationalization, security, and an intuitive user interface.

## Ključne značajke / Key Features

### Hrvatski
- Upravljanje korisnicima i uloge
- Sigurna autentikacija i autorizacija
- Internacionalizacija (višejezičnost)
- Slanje e-mailova putem PHPMailer-a
- Moderan i responzivan UI/UX
- Detaljna struktura ruta i modela
- Podrška za migracije baze podataka

### English
- User and role management
- Secure authentication and authorization
- Internationalization (multilingual support)
- Email sending via PHPMailer
- Modern and responsive UI/UX
- Detailed routing and model structure
- Database migration support

## Tehnologije / Technologies

### Hrvatski
- PHP
- PHPMailer
- MySQL / MariaDB
- HTML5, CSS3, JavaScript
- Bootstrap (ili drugi CSS framework)
- Composer

### English
- PHP
- PHPMailer
- MySQL / MariaDB
- HTML5, CSS3, JavaScript
- Bootstrap (or another CSS framework)
- Composer

## Struktura projekta / Project structure

### Hrvatski
- `/app` - Glavni kod aplikacije (kontroleri, modeli, servisi)
- `/config` - Konfiguracijske datoteke
- `/migrations` - Skripte za migraciju baze podataka
- `/public` - Javne datoteke, ulazna točka aplikacije
- `/resources` - View predlošci i jezične datoteke
- `/routes` - Definicija ruta
- `/vendor` - Composer paketi

### English
- `/app` - Main application code (controllers, models, services)
- `/config` - Configuration files
- `/migrations` - Database migration scripts
- `/public` - Public files, application entry point
- `/resources` - View templates and language files
- `/routes` - Route definitions
- `/vendor` - Composer packages

## Instalacija i konfiguracija / Installation and configuration

### Hrvatski
1. Klonirajte repozitorij:
   `git clone <url>`
2. Instalirajte Composer pakete:
   `composer install`
3. Konfigurirajte `.env` datoteku s postavkama baze i e-mail servera
4. Pokrenite migracije baze podataka:
   `php migrate.php` ili odgovarajuća naredba
5. Pokrenite lokalni server ili postavite na produkcijski server

### English
1. Clone the repository:
   `git clone <url>`
2. Install Composer packages:
   `composer install`
3. Configure the `.env` file with database and email server settings
4. Run database migrations:
   `php migrate.php` or appropriate command
5. Start the local server or deploy to production server

## Rute / Routes

### Hrvatski
- `/login` - Prijava korisnika
- `/register` - Registracija novog korisnika
- `/logout` - Odjava
- `/dashboard` - Glavna nadzorna ploča
- `/users` - Upravljanje korisnicima
- `/email/send` - Slanje e-mailova

### English
- `/login` - User login
- `/register` - New user registration
- `/logout` - Logout
- `/dashboard` - Main dashboard
- `/users` - User management
- `/email/send` - Send emails

## Modeli i migracije / Models and migrations

### Hrvatski
- Modeli definiraju entitete poput korisnika, uloga, i e-mail predložaka
- Migracije omogućuju verzioniranje i upravljanje bazom podataka
- Svaka migracija sadrži metode za kreiranje i poništavanje promjena

### English
- Models define entities such as users, roles, and email templates
- Migrations enable versioning and management of the database
- Each migration contains methods for applying and rolling back changes

## Autentikacija i sigurnost / Authentication and security

### Hrvatski
- Sigurna pohrana lozinki (hashing)
- Zaštita ruta pomoću middleware-a
- Upravljanje sesijama i tokenima
- Validacija korisničkih podataka

### English
- Secure password storage (hashing)
- Route protection via middleware
- Session and token management
- User data validation

## Internacionalizacija / Internationalization

### Hrvatski
- Podrška za više jezika kroz jezične datoteke
- Dinamičko učitavanje prijevoda ovisno o korisnikovim postavkama
- Omogućuje lako dodavanje novih jezika

### English
- Support for multiple languages via language files
- Dynamic loading of translations based on user settings
- Easy addition of new languages

## UI i UX

### Hrvatski
- Responzivan dizajn prilagođen svim uređajima
- Intuitivna navigacija i korisnički tok
- Korištenje modernih CSS frameworka za bolji izgled

### English
- Responsive design for all devices
- Intuitive navigation and user flow
- Use of modern CSS frameworks for enhanced appearance

## E-mail (PHPMailer)

### Hrvatski
- Integracija PHPMailer biblioteke za slanje e-mailova
- Podrška za SMTP autentikaciju
- Slanje HTML i plain-text e-mailova
- Konfiguracija putem `.env` datoteke

### English
- PHPMailer library integration for sending emails
- Support for SMTP authentication
- Sending HTML and plain-text emails
- Configuration via `.env` file

## Razvojne napomene / Development notes

### Hrvatski
- Koristiti verzioniranje koda (git)
- Testirati svaku funkcionalnost prije deploya
- Pratiti sigurnosne preporuke i ažurirati ovisnosti

### English
- Use version control (git)
- Test each feature before deployment
- Follow security best practices and update dependencies

## TODO / Roadmap

### Hrvatski
- Dodati podršku za višejezične e-mail predloške
- Implementirati uloge i dopuštenja na granularnijoj razini
- Poboljšati UI s dodatnim animacijama i obavijestima

### English
- Add support for multilingual email templates
- Implement more granular roles and permissions
- Enhance UI with additional animations and notifications

## Licenca / License

### Hrvatski
Ovaj projekt je licenciran pod MIT licencom. Pogledajte datoteku LICENSE za detalje.

### English
This project is licensed under the MIT License. See the LICENSE file for details.

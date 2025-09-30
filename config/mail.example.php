<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Primjer konfiguracije za SMTP mail pomoću PHPMailer-a.
 * Ovu datoteku treba kopirati kao 'mail.php' i popuniti stvarnim SMTP podacima
 * kako bi aplikacija mogla ispravno slati email poruke.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Example configuration for SMTP mail using PHPMailer.
 * This file should be copied as 'mail.php' and filled with real SMTP credentials
 * for the application to be able to send emails properly.
 */
return [
  'smtp_host' => 'your.smtp.host', // HR: Adresa SMTP poslužitelja / EN: SMTP server address
  'smtp_port' => 587, // HR: Port (587 za TLS, 465 za SSL) / EN: Port (587 for TLS, 465 for SSL)
  'smtp_user' => 'your-username', // HR: Korisničko ime za autentikaciju / EN: Username for authentication
  'smtp_pass' => 'your-password', // HR: Lozinka za autentikaciju / EN: Password for authentication
  'smtp_secure' => 'tls', // HR: 'tls' ili 'ssl' ovisno o poslužitelju / EN: 'tls' or 'ssl' depending on server
  'from_email' => 'noreply@example.com', // HR: Email adresa pošiljatelja / EN: Sender email address
  'from_name' => 'Your Application', // HR: Ime pošiljatelja prikazano primatelju / EN: Sender name shown to recipient
];

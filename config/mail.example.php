<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Konfiguracija za SMTP mail.
 * Ovaj fajl treba kopirati kao mail.php i popuniti stvarnim SMTP podacima
 * da bi aplikacija ispravno radila.
 * Ovdje definiramo postavke za slanje emailova pomoću PHPMailer-a.
 * - smtp_host: adresa SMTP poslužitelja
 * - smtp_port: port (587 za TLS, 465 za SSL)
 * - smtp_user: korisničko ime za autentikaciju
 * - smtp_pass: lozinka za autentikaciju
 * - smtp_secure: 'tls' ili 'ssl' ovisno o poslužitelju
 * - from_email: email adresa koja se prikazuje kao pošiljatelj
 * - from_name: ime pošiljatelja koje se prikazuje primatelju
 *
 * ===========================
 *  English
 * ===========================
 * SMTP mail configuration.
 * This file should be copied as mail.php and filled with real SMTP credentials
 * for the application to work properly.
 * Here we define settings for sending emails using PHPMailer.
 * - smtp_host: SMTP server address
 * - smtp_port: port (587 for TLS, 465 for SSL)
 * - smtp_user: username for authentication
 * - smtp_pass: password for authentication
 * - smtp_secure: 'tls' or 'ssl' depending on server
 * - from_email: email address shown as sender
 * - from_name: sender name shown to recipient
 */
return [
  'smtp_host' => 'your.smtp.host',
  'smtp_port' => 587,
  'smtp_user' => 'your-username',
  'smtp_pass' => 'your-password',
  'smtp_secure' => 'tls',
  'from_email' => 'noreply@example.com',
  'from_name' => 'Your Application',
];

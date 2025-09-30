<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Mailer klasa za slanje emailova putem SMTP-a koristeći PHPMailer.
 * Koristi postavke definirane u config/mail.php.
 * Podržava slanje HTML i plain text emailova.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Mailer class for sending emails via SMTP using PHPMailer.
 * Uses settings defined in config/mail.php.
 * Supports sending HTML and plain text emails.
 */
class Mailer
{
  private PHPMailer $mail;

  /**
   * HR: Konstruktor inicijalizira PHPMailer instancu s konfiguracijom iz config/mail.php.
   * EN: Constructor initializes PHPMailer instance with configuration from config/mail.php.
   *
   * @param array $config HR: Konfiguracija za SMTP (host, port, korisnik, lozinka, sigurnost, from_email, from_name)
   *                      / EN: SMTP configuration (host, port, user, password, security, from_email, from_name)
   * @return void
   * @throws Exception HR: Ako inicijalizacija PHPMailera ne uspije / EN: If PHPMailer initialization fails
   */
  public function __construct(array $config)
  {
      $this->mail = new PHPMailer(true);
      $this->mail->isSMTP(); // HR: Koristi SMTP / EN: Use SMTP
      $this->mail->Host = $config['smtp_host']; // HR: SMTP poslužitelj / EN: SMTP host
      $this->mail->Port = $config['smtp_port']; // HR: SMTP port / EN: SMTP port
      $this->mail->SMTPAuth = true; // HR: Uključi SMTP autentikaciju / EN: Enable SMTP authentication
      $this->mail->Username = $config['smtp_user']; // HR: Korisničko ime / EN: Username
      $this->mail->Password = $config['smtp_pass']; // HR: Lozinka / EN: Password
      $this->mail->SMTPSecure = $config['smtp_secure']; // HR: Tip enkripcije (TLS/SSL) / EN: Encryption type (TLS/SSL)
      $this->mail->CharSet = 'UTF-8'; // HR: Postavi UTF-8 za ispravne znakove / EN: Set UTF-8 for proper characters

      $this->mail->setFrom($config['from_email'], $config['from_name']);
      // HR: Postavi adresu i ime pošiljatelja / EN: Set sender address and name
  }

  /**
   * HR: Šalje email na zadanu adresu s predmetom i HTML/tekstualnim sadržajem.
   * EN: Sends an email to the given address with subject and HTML/text content.
   *
   * @param string $to HR: Email adresa primatelja / EN: Recipient email address
   * @param string $subject HR: Predmet emaila / EN: Email subject
   * @param string $bodyHtml HR: HTML sadržaj poruke / EN: HTML message content
   * @param string|null $bodyText HR: Plain text verzija poruke (opcionalno) / EN: Plain text version of the message (optional)
   * @return bool HR: True ako je email uspješno poslan, inače false / EN: True if email was sent successfully, otherwise false
   */
  public function send(string $to, string $subject, string $bodyHtml, ?string $bodyText = null): bool
  {
      try {
          $this->mail->clearAddresses(); // HR: Očisti prethodne adrese / EN: Clear previous addresses
          $this->mail->addAddress($to); // HR: Dodaj primatelja / EN: Add recipient
          $this->mail->Subject = $subject; // HR: Postavi predmet / EN: Set subject

          if ($bodyText) {
              $this->mail->AltBody = $bodyText;
              // HR: Dodaj plain text verziju (ako postoji) / EN: Add plain text version (if exists)
          }

          $this->mail->Body = $bodyHtml; // HR: HTML sadržaj / EN: HTML content
          $this->mail->isHTML(); // HR: Omogući HTML format / EN: Enable HTML format

          return $this->mail->send(); // HR: Pošalji email / EN: Send email
      } catch (Exception $e) {
          error_log('Mail error: ' . $e->getMessage());
          // HR: Zapiši grešku u log / EN: Log the error
          return false; // HR: Ako ne uspije, vrati false / EN: If failed, return false
      }
  }
}

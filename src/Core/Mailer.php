<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Mailer klasa za slanje emailova putem SMTP-a koristeći PHPMailer.
 * Koristi postavke definirane u config/mail.php.
 * Omogućuje slanje HTML i plain text emailova.
 *
 * ===========================
 *  English
 * ===========================
 * Mailer class for sending emails via SMTP using PHPMailer.
 * Uses settings defined in config/mail.php.
 * Supports sending HTML and plain text emails.
 */
class Mailer
{
  private PHPMailer $mail;

  /**
   * @throws Exception
   */
  public function __construct(array $config)
  {
    $this->mail = new PHPMailer(true);
    $this->mail->isSMTP();
    $this->mail->Host = $config['smtp_host'];
    $this->mail->Port = $config['smtp_port'];
    $this->mail->SMTPAuth = true;
    $this->mail->Username = $config['smtp_user'];
    $this->mail->Password = $config['smtp_pass'];
    $this->mail->SMTPSecure = $config['smtp_secure'];
    $this->mail->CharSet = 'UTF-8';

    $this->mail->setFrom($config['from_email'], $config['from_name']);
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Šalje email na zadanu adresu.
   *
   * ===========================
   *  English
   * ===========================
   * Sends an email to the given address.
   *
   * @param string $to Primatelj emaila / Recipient email
   * @param string $subject Predmet emaila / Email subject
   * @param string $bodyHtml HTML sadržaj / HTML content
   * @param string|null $bodyText Plain tekst verzija (opcionalno) / Plain text version (optional)
   * @return bool True ako je slanje uspjelo, false inače / True if sent successfully, false otherwise
   */
  public function send(string $to, string $subject, string $bodyHtml, ?string $bodyText = null): bool
  {
    try {
      $this->mail->clearAddresses();
      $this->mail->addAddress($to);
      $this->mail->Subject = $subject;

      if ($bodyText) {
        $this->mail->AltBody = $bodyText;
      }
      $this->mail->Body = $bodyHtml;
      $this->mail->isHTML();

      return $this->mail->send();
    } catch (Exception $e) {
      error_log('Mail error: ' . $e->getMessage());
      return false;
    }
  }
}

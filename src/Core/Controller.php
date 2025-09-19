<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Osnovni (base) kontroler kojeg nasljeđuju svi ostali kontroleri.
 * Pruža zajedničke metode:
 * - render: uključuje zadani view unutar layouta, prosljeđujući parametre
 * - redirect: preusmjerava korisnika na zadani URL i prekida izvršavanje
 *
 * ===========================
 *  English
 * ===========================
 * Base controller extended by all other controllers.
 * Provides common methods:
 * - render: includes the given view inside the layout, passing parameters
 * - redirect: redirects the user to the given URL and stops execution
 */

namespace App\Core;

class Controller
{
  protected function render(string $view, array $params = []): void
  {
    // Uključi view unutar glavnog layouta i proslijedi parametre. / Include the view inside the main layout and pass parameters.
    extract($params);
    ob_start();
    include __DIR__ . '/../../views/' . $view . '.php';
    $content = ob_get_clean();
    include __DIR__ . '/../../views/layout.php';
  }

  protected function redirect(string $to): void
  {
    // Preusmjeri korisnika na zadani URL i zaustavi izvršavanje. / Redirect the user to the given URL and stop execution.
    header('Location: ' . $to);
    exit;
  }
}

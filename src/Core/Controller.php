<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Osnovni (base) kontroler kojeg nasljeđuju svi ostali kontroleri.
 * Sadrži zajedničke metode za renderiranje pogleda i preusmjeravanje.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Base controller extended by all other controllers.
 * Contains common methods for rendering views and redirecting.
 */

namespace App\Core;

// HR: Osnovni kontroler / EN: Base controller
use RuntimeException;

class Controller
{
  /**
   * HR: Renderira zadani view unutar glavnog layouta i prosljeđuje parametre.
   * EN: Renders the given view inside the main layout and passes parameters.
   *
   * @param string $view HR: Naziv view datoteke (bez .php ekstenzije) / EN: Name of the view file (without .php extension)
   * @param array $params HR: Parametri koji se prosljeđuju view-u kao varijable / EN: Parameters passed to the view as variables
   * @return void
   * @throws RuntimeException HR: Ako view datoteka ne postoji / EN: If the view file does not exist
   */
  protected function render(string $view, array $params = []): void
  {
    extract($params);
    // HR: Pretvara ključeve iz niza $params u varijable za view / EN: Converts keys from $params array into variables for the view

    ob_start();
    // HR: Pokreće output buffering / EN: Start output buffering

    include __DIR__ . '/../../views/' . $view . '.php';
    // HR: Uključi odgovarajući view file / EN: Include the corresponding view file

    $content = ob_get_clean();
    // HR: Dohvaća generirani sadržaj i čisti buffer / EN: Get generated content and clear buffer

    include __DIR__ . '/../../views/layout.php';
    // HR: Uključi glavni layout i u njega ubaci sadržaj / EN: Include the main layout and insert the content
  }
}

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Ova datoteka sadrži globalne JavaScript helpere za aplikaciju.
 * Uključuje:
 * - CSRF token (ubacuje se iz layout.php)
 * - Funkciju showMessage() za prikaz flash poruka korisniku
 *
 * ===========================
 *  English
 * ===========================
 * This file contains global JavaScript helpers for the application.
 * It includes:
 * - CSRF token (injected from layout.php)
 * - showMessage() function for displaying flash messages to the user
 */

// CSRF token (bit će ubacivan iz layout.php)
// Will be injected from layout.php
// Example injection: <script>const csrfToken = "<?= App\Core\Csrf::token() ?>";</script>

// Flash message helper
function showMessage(message, type = 'info') {
  // Dohvati kontejner za poruke / Get the container for messages
  const container = document.getElementById('flash-messages');
  if (!container) return;

  // Kreiraj novi alert element s Bootstrap klasama / Create a new alert element with Bootstrap classes
  const wrapper = document.createElement('div');
  wrapper.className = `alert alert-${type} alert-dismissible fade show`;
  wrapper.role = 'alert';
  wrapper.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

  // Dodaj alert u kontejner / Append the alert to the container
  container.appendChild(wrapper);

  // Automatski sakrij poruku nakon 5 sekundi / Auto-hide the message after 5 seconds
  setTimeout(() => {
    wrapper.remove();
  }, 5000);
}

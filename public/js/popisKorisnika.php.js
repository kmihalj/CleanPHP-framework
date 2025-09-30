/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * JavaScript datoteka za upravljanje modalnim prozorima na stranici popisa korisnika.
 * Omogućuje prikaz i popunjavanje podataka u modalima za brisanje i resetiranje lozinke korisnika.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * JavaScript file for handling modal dialogs on the user list page.
 * Enables displaying and populating modals for deleting and resetting a user's password.
 */

document.addEventListener('DOMContentLoaded', () => {
  // HR: Elementi za DELETE modal (brisanje korisnika) / EN: Elements for DELETE modal (user deletion)
  const deleteModal = document.getElementById('deleteModal');
  const deleteUserName = document.getElementById('deleteUserName');
  const deleteUserUuid = document.getElementById('deleteUserUuid');

  // HR: Elementi za RESET modal (resetiranje lozinke) / EN: Elements for RESET modal (password reset)
  const resetModal = document.getElementById('resetModal');
  const resetUserName = document.getElementById('resetUserName');
  const resetUserUuid = document.getElementById('resetUserUuid');

  // HR: Postavljanje podataka kada se otvori DELETE modal / EN: Populate data when DELETE modal is shown
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      deleteUserName.textContent = button.getAttribute('data-name');
      deleteUserUuid.value = button.getAttribute('data-uuid');
    });
  }

  // HR: Postavljanje podataka kada se otvori RESET modal / EN: Populate data when RESET modal is shown
  if (resetModal) {
    resetModal.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      resetUserName.textContent = button.getAttribute('data-name');
      resetUserUuid.value = button.getAttribute('data-uuid');
    });
  }
});

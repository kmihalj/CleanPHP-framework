document.addEventListener('DOMContentLoaded', () => {
  // Kada je DOM sadržaj učitan, inicijaliziramo modale
  // When the DOM content is loaded, initialize the modals

  // Inicijalizacija modala za brisanje korisnika
  // Initialize the delete user modal
  // Mapa atributa: deleteUserName iz data-name, deleteUserUuid iz data-uuid
  // Attribute mapping: deleteUserName from data-name, deleteUserUuid from data-uuid
  setupModal('deleteModal', { deleteUserName: 'data-name', deleteUserUuid: 'data-uuid' });

  // Inicijalizacija modala za resetiranje korisnika
  // Initialize the reset user modal
  // Mapa atributa: resetUserName iz data-name, resetUserUuid iz data-uuid
  // Attribute mapping: resetUserName from data-name, resetUserUuid from data-uuid
  setupModal('resetModal', { resetUserName: 'data-name', resetUserUuid: 'data-uuid' });

  // Inicijalizacija modala za uređivanje korisnika
  // Initialize the edit user modal
  // Mapa atributa: editUserUuid iz data-uuid, editIme iz data-ime, editPrezime iz data-prezime,
  // editUsername iz data-username, editEmail iz data-email, editOib iz data-oib
  // Attribute mapping: editUserUuid from data-uuid, editIme from data-ime, editPrezime from data-prezime,
  // editUsername from data-username, editEmail from data-email, editOib from data-oib
  setupModal('editModal', {
    editUserUuid: 'data-uuid',
    editIme: 'data-ime',
    editPrezime: 'data-prezime',
    editUsername: 'data-username',
    editEmail: 'data-email',
    editOib: 'data-oib',
    editRoles: 'data-role-uuid'
  });

  // Nakon inicijalizacije edit modala dodaj logiku za check-boxeve rola
  // After initializing the edit modal, add logic for role checkboxes
  const editModalEl = document.getElementById('editModal');
  if (editModalEl) {
    editModalEl.addEventListener('show.bs.modal', (event) => {
      const button = event.relatedTarget;
      if (!button) return;

      // Dohvati sve role korisnika iz data atributa
      // Get all user roles from data attribute
      let roleUuidsRaw = button.getAttribute('data-role-uuid') || '';
      roleUuidsRaw = roleUuidsRaw.replace(/^["']|["']$/g, '');
      let userRoles = roleUuidsRaw
        .split(',')
        .map(r => r.trim())
        .filter(r => r !== '');

      // Resetiraj sve checkboxeve
      // Reset all checkboxes
      const roleCheckboxes = editModalEl.querySelectorAll('input[name="editRole[]"]');
      if (!roleCheckboxes || roleCheckboxes.length === 0) {
        console.warn('No role checkboxes found with selector input[name="editRole[]"]');
      }
      roleCheckboxes.forEach(cb => {
        cb.checked = userRoles.includes(cb.value.trim());
      });
    });
  }

  // Ako postoje greške iz validacije za edit, automatski otvori modal
  // If there are validation errors for edit, automatically open the modal
  const hasEditErrors = document.getElementById('edit-verify')?.getAttribute('data-has-edit-errors');
  if (hasEditErrors === '1') {
    const editModalEl = document.getElementById('editModal');
    if (editModalEl) {
      const editModal = new bootstrap.Modal(editModalEl);
      editModal.show();
    }
  }
});

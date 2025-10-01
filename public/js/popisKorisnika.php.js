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
  // editUsername iz data-username, editEmail iz data-email, editOib iz data-oib, editRole iz data-role-uuid
  // Attribute mapping: editUserUuid from data-uuid, editIme from data-ime, editPrezime from data-prezime,
  // editUsername from data-username, editEmail from data-email, editOib from data-oib, editRole from data-role-uuid
  setupModal('editModal', {
    editUserUuid: 'data-uuid',
    editIme: 'data-ime',
    editPrezime: 'data-prezime',
    editUsername: 'data-username',
    editEmail: 'data-email',
    editOib: 'data-oib',
    editRole: 'data-role-uuid'
  });

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

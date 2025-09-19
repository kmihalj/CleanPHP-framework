/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Ova skripta omogućuje inline uređivanje korisnika na stranici popisa.
 * Podržava:
 * - Klik na ikonu ✏️ za uređivanje retka
 * - Spremanje (💾) ili odustajanje (Escape)
 * - Spremanje promjena tipkom Enter
 * - Dvoklik na ćeliju za ulazak u uređivanje i fokusiranje polja
 * - AJAX spremanje promjena uz prikaz poruka
 *
 * ===========================
 *  English
 * ===========================
 * This script enables inline editing of users on the list page.
 * It supports:
 * - Clicking the ✏️ icon to edit a row
 * - Saving (💾) or canceling (Escape)
 * - Saving changes with the Enter key
 * - Double-clicking a cell to enter edit mode and focus the field
 * - AJAX saving of changes with message display
 */
/**
 * @typedef {Object} Modal
 * @property {(el: HTMLElement) => Modal} getInstance
 * @property {() => void} hide
 * @property {*} prototype
 *
 * @typedef {Object} Alert
 * @property {() => void} close
 * @property {*} prototype
 *
 * @typedef {{ Modal: Modal, Alert: { new(el: HTMLElement): Alert } }} Bootstrap
 * @type {Bootstrap}
 */
const bootstrap = window.bootstrap;
// HR: Pretpostavlja se da su csrfToken i showMessage definirani globalno.
// EN: Assumes csrfToken and showMessage are defined globally.
document.addEventListener('DOMContentLoaded', () => {
  let currentEditingRow = null;

  // HR: Dodaj event listener na svaku ikonu za uređivanje.
  // EN: Add event listener to each edit icon.
  document.querySelectorAll('.action-edit').forEach(icon => {
    // HR: Klik na ikonu za uređivanje retka - ulazak u režim uređivanja ili spremanje/odustajanje.
    // EN: Click on the edit icon - enter edit mode or save/cancel editing.
    icon.addEventListener('click', e => {
      e.preventDefault();
      const tr = icon.closest('tr');
      if (!tr) return;

      // HR: Ako je već drugi red u editiranju, zatvori ga.
      // EN: If another row is being edited, close it.
      if (currentEditingRow && currentEditingRow !== tr) {
        finishEditing(currentEditingRow, false);
      }

      if (tr.classList.contains('editing')) {
        // HR: Ako je red u režimu uređivanja, pokušaj spremiti ili odustati.
        // EN: If row is in edit mode, try to save or cancel.
        finishEditing(tr, true);
        currentEditingRow = null;
        return;
      }

      // HR: Uđi u režim uređivanja - promijeni ikonu i zamijeni ćelije input poljima.
      // EN: Enter edit mode - change icon and replace cells with input fields.
      tr.classList.add('editing');
      icon.textContent = '💾';
      currentEditingRow = tr;

      const editableFields = ['first_name', 'last_name', 'username', 'email'];
      editableFields.forEach(field => {
        const td = tr.querySelector(`td[data-field="${field}"]`);
        if (td) {
          const oldValue = td.textContent.trim();
          td.dataset.oldValue = oldValue;
          td.innerHTML = `<input type="text" class="form-control form-control-sm" value="${oldValue}">`;
        }
      });

      const roleTd = tr.querySelector('td[data-field="role"]');
      if (roleTd) {
        const oldRole = roleTd.textContent.trim();
        roleTd.dataset.oldValue = oldRole;
        roleTd.innerHTML = `
                    <select class="form-select form-select-sm">
                        <option value="Registriran" ${oldRole === 'Registriran' ? 'selected' : ''}>Registriran</option>
                        <option value="Korisnik" ${oldRole === 'Korisnik' ? 'selected' : ''}>Korisnik</option>
                        <option value="Admin" ${oldRole === 'Admin' ? 'selected' : ''}>Admin</option>
                    </select>
                `;
      }

      // HR: Dodaj prečace tipki - Enter za spremanje, Escape za odustajanje.
      // EN: Add keyboard shortcuts - Enter to save, Escape to cancel.
      const inputs = tr.querySelectorAll('input, select');
      inputs.forEach(input => {
        input.addEventListener('keydown', e => {
          if (e.key === 'Enter') {
            e.preventDefault();
            finishEditing(tr, true);
            currentEditingRow = null;
          } else if (e.key === 'Escape') {
            e.preventDefault();
            finishEditing(tr, false);
            currentEditingRow = null;
          }
        });
      });
    });
  });

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Funkcija za završetak uređivanja jednog retka tablice.
   * - Ako je `attemptSave = true` i postoje promjene, šalje AJAX zahtjev za spremanje.
   * - Ako spremanje nije uspješno, red ostaje u režimu uređivanja i prikazuju se greške.
   * - Ako je `attemptSave = false` ili nema promjena, vraćaju se stare vrijednosti.
   *
   * @param {HTMLTableRowElement} tr - Redak tablice koji se uređuje
   * @param {boolean} attemptSave - Ako je true, pokušaj spremiti promjene, inače odustani
   *
   * ===========================
   *  English
   * ===========================
   * Function to finish editing a table row.
   * - If `attemptSave = true` and changes exist, sends an AJAX request to save.
   * - If saving fails, keeps the row in edit mode and shows validation errors.
   * - If `attemptSave = false` or no changes, reverts to old values.
   *
   * @param {HTMLTableRowElement} tr - The table row being edited
   * @param {boolean} attemptSave - If true, attempt to save changes; otherwise cancel
   */
  /**
   * @typedef {Object} UpdateResponse
   * @property {string} status
   * @property {string} [message]
   * @property {Object.<string,string>} [fields]
   */
  function finishEditing(tr, attemptSave) {
    const icon = tr.querySelector('.action-edit');
    const editableFields = ['first_name', 'last_name', 'username', 'email', 'role'];
    let changed = false;
    const data = {csrf: typeof csrfToken !== 'undefined' ? csrfToken : '', id: tr.dataset.id};

    // HR: Provjeri promjene u poljima i pripremi podatke za slanje.
    // EN: Check changes in fields and prepare data for sending.
    editableFields.forEach(field => {
      const td = tr.querySelector(`td[data-field="${field}"]`);
      if (!td) return;
      const input = td.querySelector('input, select');
      const newValue = input ? input.value.trim() : td.textContent.trim();
      const oldValue = td.dataset.oldValue || '';
      if (newValue !== oldValue) {
        changed = true;
      }
      data[field] = newValue;
    });

    if (attemptSave && changed) {
      // HR: Ako je pokušaj spremanja i ima promjena, pošalji AJAX zahtjev.
      // EN: If attempting to save and changes exist, send AJAX request.
      fetch(`${typeof updateUrlBase !== 'undefined' ? updateUrlBase : ''}/${data.id}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
      })
        .then(res => res.json())
        .then(
          /** @param {UpdateResponse} result */
          result => {
            if (result.status === 'ok') {
              // HR: Ako je uspješno, ažuriraj prikaz s novim vrijednostima i prikaži poruku.
              // EN: If successful, update display with new values and show message.
              editableFields.forEach(field => {
                const td = tr.querySelector(`td[data-field="${field}"]`);
                if (td) td.textContent = data[field];
              });
              if (typeof showMessage === 'function') {
                showMessage(messages.saved, 'success');
              }
            } else if (result.fields) {
              // HR: Ako backend vrati greške po poljima, ostavi red u edit modu i označi problematična polja.
              // EN: If backend returns field errors, keep row in edit mode and mark problematic fields.
              editableFields.forEach(field => {
                const td = tr.querySelector(`td[data-field="${field}"]`);
                if (!td) return;
                const input = td.querySelector('input, select');
                if (input) {
                  if (result.fields[field]) {
                    input.classList.add('is-invalid');
                    // Dodaj ili ažuriraj feedback element
                    let fb = td.querySelector('.invalid-feedback');
                    if (!fb) {
                      fb = document.createElement('div');
                      fb.className = 'invalid-feedback';
                      td.appendChild(fb);
                    }
                    fb.textContent = result.fields[field];
                  } else {
                    input.classList.remove('is-invalid');
                    const fb = td.querySelector('.invalid-feedback');
                    if (fb) fb.remove();
                  }
                }
              });
              if (typeof showMessage === 'function') {
                showMessage(messages.error, 'danger');
              }
              return void 0; // ostani u edit modu
            } else {
              // HR: Ako je opća greška, prikaži poruku i vrati stare vrijednosti.
              // EN: If general error, show message and revert to old values.
              if (typeof showMessage === 'function') {
                showMessage(result.message || messages.error, 'danger');
              }
              editableFields.forEach(field => {
                const td = tr.querySelector(`td[data-field="${field}"]`);
                if (td) td.textContent = td.dataset.oldValue;
              });
            }
          })
        .catch(() => {
          // HR: Ako je došlo do mrežne greške, prikaži poruku i vrati stare vrijednosti.
          // EN: If network error occurs, show message and revert to old values.
          if (typeof showMessage === 'function') {
            showMessage(messages.network, 'danger');
          }
          editableFields.forEach(field => {
            const td = tr.querySelector(`td[data-field="${field}"]`);
            if (td) td.textContent = td.dataset.oldValue;
          });
        });
    } else {
      // HR: Nema promjena ili odustajanje → vrati stare vrijednosti.
      // EN: No changes or cancel → revert to old values.
      editableFields.forEach(field => {
        const td = tr.querySelector(`td[data-field="${field}"]`);
        if (td) {
          td.textContent = td.dataset.oldValue;
        }
      });
    }

    tr.classList.remove('editing');
    if (icon) icon.textContent = '✏️';
  }

  // HR: Omogući dvoklik na ćeliju za ulazak u režim uređivanja i fokusiranje polja.
  // EN: Enable double-click on a cell to enter edit mode and focus the field.
  document.querySelectorAll('td[data-field]').forEach(td => {
    // HR: Dvoklik na ćeliju - ulazak u režim uređivanja retka i fokusiranje na polje.
    // EN: Double-click on a cell - enter edit mode for the row and focus the field.
    td.addEventListener('dblclick', () => {
      const tr = td.closest('tr');
      if (!tr) return;

      // HR: Ako je već drugi red u editiranju, zatvori ga.
      // EN: If another row is being edited, close it.
      if (currentEditingRow && currentEditingRow !== tr) {
        finishEditing(currentEditingRow, false);
      }

      // HR: Ako je ovaj red već u režimu uređivanja, samo fokusiraj input.
      // EN: If this row is already in edit mode, just focus the input.
      if (tr.classList.contains('editing')) {
        const input = td.querySelector('input, select');
        if (input) input.focus();
        return;
      }

      // HR: Pokreni klik na ikonu za uređivanje da uđeš u režim uređivanja.
      // EN: Trigger the edit icon click to enter edit mode.
      const editIcon = tr.querySelector('.action-edit');
      if (editIcon) {
        editIcon.click();

        // HR: Fokusiraj input/select u dvokliknutoj ćeliji nakon ulaska u režim uređivanja.
        // EN: Focus the input/select in the double-clicked cell after entering edit mode.
        setTimeout(() => {
          const input = td.querySelector('input, select');
          if (input) input.focus();
        }, 0);
      }
    });
  });

  // HR: Logika za modal za resetiranje lozinke
  // EN: Reset Password modal logic
  let resetUserId = null;
  document.querySelectorAll('.action-reset').forEach(icon => {
    // HR: Klik na ikonu za reset lozinke - priprema modala s podacima korisnika.
    // EN: Click on the reset password icon - prepare modal with user data.
    icon.addEventListener('click', e => {
      e.preventDefault();
      const tr = icon.closest('tr');
      if (!tr) return;
      resetUserId = tr.dataset.id;
      const name = tr.querySelector('td[data-field="first_name"]').textContent.trim() + ' ' +
        tr.querySelector('td[data-field="last_name"]').textContent.trim();
      const email = tr.querySelector('td[data-field="email"]').textContent.trim();
      const template = (window.translations && window.translations.reset_message)
        ? window.translations.reset_message
        : 'Korisniku "%s" će biti generirana nova lozinka i poslana mailom na adresu "%s".';
      const msg = template.replace('%s', name).replace('%s', email);
      const msgEl = document.getElementById('resetPasswordMessage');
      if (msgEl) msgEl.textContent = msg;
      const modalEl = document.getElementById('resetPasswordModal');
      if (modalEl) {
        // Modal is triggered automatically by data-bs-toggle/data-bs-target
      }
    });
  });

  /**
   * @description
   * Šalje AJAX zahtjev za resetiranje lozinke korisnika.
   * Sends an AJAX request to reset the user's password.
   */
  const confirmBtn = document.getElementById('confirmResetPassword');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
      if (!resetUserId) return;
      fetch(`${typeof resetUrlBase !== 'undefined' ? resetUrlBase : ''}/${resetUserId}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf: typeof csrfToken !== 'undefined' ? csrfToken : ''})
      })
        .then(res => res.json())
        .then(result => {
          const modalEl = document.getElementById('resetPasswordModal');
          if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }
          if (result.status === 'ok') {
            if (typeof showMessage === 'function') {
              showMessage(result.message || messages.resetSuccess, 'success');
            }
          } else {
            if (typeof showMessage === 'function') {
              showMessage(result.message || messages.resetError, 'danger');
            }
          }
        })
        .catch(() => {
          if (typeof showMessage === 'function') {
            showMessage(messages.networkReset, 'danger');
          }
        });
    });
  }

  // HR: Logika za modal za brisanje korisnika
  // EN: Delete User modal logic
  let deleteUserId = null;
  document.querySelectorAll('.action-delete').forEach(icon => {
    // HR: Klik na ikonu za brisanje - priprema modala s podacima korisnika.
    // EN: Click on the delete icon - prepare modal with user data.
    icon.addEventListener('click', e => {
      e.preventDefault();
      const tr = icon.closest('tr');
      if (!tr) return;
      deleteUserId = tr.dataset.id;
      const firstName = tr.querySelector('td[data-field="first_name"]').textContent.trim();
      const lastName = tr.querySelector('td[data-field="last_name"]').textContent.trim();
      const template = (window.translations && window.translations.delete_message)
        ? window.translations.delete_message
        : 'Korisnik "%s %s" će biti obrisan.';
      const msg = template.replace('%s', firstName).replace('%s', lastName);
      const msgEl = document.getElementById('deleteUserMessage');
      if (msgEl) msgEl.textContent = msg;

      const form = document.getElementById('deleteUserForm');
      if (form) {
        form.action = `${typeof deleteUrlBase !== 'undefined' ? deleteUrlBase : ''}/${deleteUserId}`;
      }

    });
  });

});

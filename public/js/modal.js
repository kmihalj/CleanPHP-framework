/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Pomoćna funkcija za jednostavno popunjavanje modalnih prozora
 * na temelju data-* atributa gumba koji otvara modal.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Helper function for easily populating modal dialogs
 * based on the data-* attributes of the triggering button.
 *
 * HR: Podržava i checkbox grupe (npr. role) koristeći CSV vrijednosti iz data-* atributa.
 * EN: Also supports checkbox groups (e.g., roles) using CSV values from data-* attributes.
 */

function setupModal(modalId, mappings) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  modal.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    if (!button) return;

    // Prolazimo kroz svaki par [targetId, attr] u objektu mappings
    // Iterate over each [targetId, attr] pair in the mappings object
    for (const [targetId, attr] of Object.entries(mappings)) {
      const el = document.getElementById(targetId);
      if (!el) continue;

      const dataVal = button.getAttribute(attr) || '';

      // HR: Jedan checkbox/radio – postavi checked prema dataVal (podržava '1', 'true' ili točnu vrijednost).
      // EN: Single checkbox/radio – set checked based on dataVal ('1', 'true' or exact value).
      if (el.tagName === 'INPUT' && (el.type === 'checkbox' || el.type === 'radio')) {
        const valLower = (typeof dataVal === 'string') ? dataVal.toLowerCase() : '';
        el.checked = (dataVal === '1') || (valLower === 'true') || (dataVal === el.value);
        continue;
      }

      // HR: Standardni form elementi – upiši vrijednost.
      // EN: Standard form controls – set the value.
      if (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') {
        el.value = dataVal;
      } else {
        // HR: Inače tretiraj kao tekstualni čvor.
        // EN: Otherwise treat as a text node.
        el.textContent = dataVal;
      }
    }
  });
}

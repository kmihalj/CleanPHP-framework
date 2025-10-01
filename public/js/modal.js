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

      // Provjeravamo postoji li element s danim ID-jem
      // Check if an element with the given ID exists
      if (el) {
        // Ako je element input ili select, postavljamo njegovu vrijednost
        // If the element is an input or select, set its value
        if (el.tagName === 'INPUT' || el.tagName === 'SELECT') {
          el.value = button.getAttribute(attr) || '';
        } else {
          // Inače, postavljamo tekstualni sadržaj elementa
          // Otherwise, set the text content of the element
          el.textContent = button.getAttribute(attr) || '';
        }
      }
    }
  });
}

<!-- ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * View datoteka za korisnički dashboard (naslovnicu nakon prijave).
 * - Prikazuje naslov i poruku dobrodošlice.
 * - Sadržaj je dostupan samo prijavljenim korisnicima (zaštićena ruta).
 *
 * ===========================
 *  English
 * ===========================
 * View file for the user dashboard (homepage after login).
 * - Displays a heading and a welcome message.
 * - Content is only accessible to logged-in users (protected route).
-->

<h1><?= _t('Naslovnica') ?></h1>
<p><?= _t('Dobrodošli! Uspješno ste prijavljeni.') ?></p>
<p><?= _t('Ovo je zaštićena stranica vidljiva samo prijavljenim korisnicima.') ?></p>

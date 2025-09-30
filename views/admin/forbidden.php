<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Pogled (view) forbidden.php prikazuje poruku kada korisnik
 * nema prava pristupa određenom dijelu aplikacije (samo za administratore).
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The forbidden.php view displays a message when the user
 * does not have permission to access a part of the application (admin only).
 */
?>
<div class="container mt-5">
  <div class="text-center">
    <!-- HR: Naslov poruke o zabranjenom pristupu / EN: Title of forbidden access message -->
    <h1 class="display-5 text-danger mb-4"><?= _t('Zabranjen pristup') ?></h1>

    <!-- HR: Objašnjenje da je dio aplikacije dostupan samo administratorima / EN: Explanation that section is for administrators only -->
    <p class="lead">
      <?= _t('Ovaj dio aplikacije dostupan je isključivo administratorima.') ?>
    </p>

    <!-- HR: Uputa korisniku da se obrati administratoru sustava ako misli da treba imati pristup / EN: Instruction to contact system administrator if user thinks they should have access -->
    <p>
      <?= _t('Ako mislite da bi trebali imati pristup, obratite se administratoru sustava.') ?>
    </p>
  </div>
</div>

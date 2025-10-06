<?php

use App\Core\App;
use App\Core\Csrf;

?>
<div class="row mb-3">
  <div class="col">
    <?php
    // Pripremi error i old podatke iz flash poruka
    $errors = flash_get('errors');
    $old = flash_get('old_input') ?? [];
    ?>
  </div>
</div>


<p>Ovo je buduća naslovna stranica</p>


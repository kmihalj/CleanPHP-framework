<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Kontroler za javnu početnu stranicu i dashboard nakon prijave.
 *
 * ===========================
 *  English
 * ===========================
 * Controller for the public home page and dashboard after login.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;

class TestController extends Controller
{
  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Prikazuje javnu početnu stranicu s naslovom.
   *
   * @return void
   *
   * ===========================
   *  English
   * ===========================
   * Renders the public home page with a title.
   *
   * @return void
   */
  public function index(): void
  { // Renderira javnu početnu stranicu s naslovom. / Renders the public home page with a title.
    $this->render('home/test', ['title' => _t('Početna stranica')]);
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Postavlja flash poruku uspjeha i preusmjerava korisnika na početnu stranicu.
   *
   * @return void
   *
   * ===========================
   *  English
   * ===========================
   * Sets a success flash message and redirects the user to the home page.
   *
   * @return void
   */
  public function messageSelf(): void
  {
    flash_set('success', _t('Uspjeh!'));
    header('Location: ' . App::urlFor('test.index'));
    exit;
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Postavlja flash poruku greške i preusmjerava korisnika na početnu stranicu.
   *
   * @return void
   *
   * ===========================
   *  English
   * ===========================
   * Sets an error flash message and redirects the user to the home page.
   *
   * @return void
   */
  public function errorSelf(): void
  {
    flash_set('error', _t('Greška!'));
    header('Location: ' . App::urlFor('test.index'));
    exit;
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Validira unos iz forme i vraća korisnika na početnu stranicu sa statusnim porukama i eventualnim greškama.
   *
   * @return void
   *
   * ===========================
   *  English
   * ===========================
   * Validates form input and redirects the user to the home page with status messages and possible errors.
   *
   * @return void
   */
  public function formTest(): void
  {
    $input1 = $_POST['test_input'] ?? '';
    $input2 = $_POST['test_input2'] ?? '';
    $errors = [];

    // Validation for test_input
    if (empty($input1)) {
      $errors['test_input'] = _t('Polje ne smije biti prazno.');
    } elseif (mb_strlen($input1) <= 6) {
      $errors['test_input'] = _t('Polje mora imati više od 6 znakova.');
    }

    // Validation for test_input2
    if (empty($input2)) {
      $errors['test_input2'] = _t('Polje ne smije biti prazno.');
    } elseif (mb_strlen($input2) < 3) {
      $errors['test_input2'] = _t('Polje mora imati najmanje 3 znaka.');
    }

    if (!empty($errors)) {
      flash_set('error', _t('Forma nije uspješno poslana.')); // globalni alert
      flash_set('errors', $errors);                           // detalji po poljima
      flash_set('old_input', [
        'test_input' => $input1,
        'test_input2' => $input2,
      ]);       // zadržavanje unosa
    } else {
      flash_set('success', _t('Forma uspješno poslana.'));
    }
    header('Location: ' . App::urlFor('test.index'));
    exit;
  }

}

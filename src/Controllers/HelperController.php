<?php

namespace App\Controllers;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Kontroler pomoćnih funkcija.
 * Sadrži metodu za generiranje HTML paginacije.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Helper controller.
 * Contains a method for generating HTML pagination.
 */
class HelperController
{

  /**
   * HR: Generira HTML za Bootstrap paginaciju s podrškom za sortiranje i broj zapisa po stranici.
   * EN: Generates HTML for Bootstrap pagination with support for sorting and items per page.
   *
   * @param int $page HR: Trenutni broj stranice / EN: Current page number
   * @param string|int $perPage HR: Broj zapisa po stranici ili 'all' za sve / EN: Items per page or 'all' for all
   * @param int $total HR: Ukupan broj zapisa / EN: Total number of records
   * @param string $sort HR: Kolona po kojoj se sortira / EN: Column to sort by
   * @param string $dir HR: Smjer sortiranja (asc/desc) / EN: Sorting direction (asc/desc)
   * @param string $route HR: Ruta (URL) na koju vode linkovi paginacije / EN: Route (URL) that pagination links lead to
   * @param string|null $search HR: Opcionalni pojam pretrage / EN: Optional search term
   *
   * @return string HR: HTML kod paginacije / EN: Pagination HTML code
   */
  public function renderPagination(int $page, string|int $perPage, int $total, string $sort, string $dir, string $route, ?string $search = null): string
  {
    $totalPages = ($perPage === 'all') ? 1 : ceil($total / (int)$perPage);
    // HR: Ukupan broj stranica, ako je perPage 'all' onda samo jedna stranica / EN: Total pages, if perPage is 'all' then only one page

    if ($perPage === 'all' || $totalPages <= 1) {
      return '';
      // HR: Ako je samo jedna stranica, ne generira se paginacija / EN: If only one page, no pagination is generated
    }

    $searchParam = '';
    if ($search !== null && $search !== '') {
      $searchParam = '&search=' . urlencode($search);
    }

    $renderPageItem = function($i) use ($page, $perPage, $sort, $dir, $route, $searchParam) {
      // HR: Generira HTML za jedan gumb stranice, aktivan ako je trenutna stranica / EN: Generates HTML for a page button, active if current page
      if ($i == $page) {
        return '<li class="page-item active"><span class="page-link">'.$i.'</span></li>';
      }
      $url = $route.'?page='.$i.'&per_page='.$perPage.'&sort='.$sort.'&dir='.$dir.$searchParam;
      return '<li class="page-item"><a class="page-link" href="'.$url.'">'.$i.'</a></li>';
    };
    $renderEllipsis = fn() => '<li class="page-item disabled"><span class="page-link">…</span></li>';
    // HR: Generira "..." za preskakanje stranica / EN: Generates "..." for skipped pages

    $html = '<nav><ul class="pagination justify-content-center">';
    // HR: Inicijalni HTML za Bootstrap paginaciju, centriranu / EN: Initial HTML for Bootstrap pagination, centered

    if ($totalPages <= 9) {
      // HR: Ako je broj stranica 9 ili manje, prikaži jednostavnu paginaciju bez strelica i elipsi / EN: If total pages are 9 or less, show simple pagination without arrows and ellipses
      for ($i = 1; $i <= $totalPages; $i++) {
        $html .= $renderPageItem($i);
      }
    } else {
      // Prev
      $html .= '<li class="page-item '.($page <= 1 ? 'disabled' : '').'">';
      // HR: Dodaj gumb za prethodnu stranicu, onemogućen ako smo na prvoj / EN: Add previous page button, disabled if on first page
      $html .= '<a class="page-link" href="'.$route.'?page='.max(1, $page - 1).'&per_page='.$perPage.'&sort='.$sort.'&dir='.$dir.$searchParam.'">&laquo;</a></li>';

      $html .= $renderPageItem(1);
      if ($page <= 3 || $page >= $totalPages - 2) {
        // HR: Ako smo blizu početka ili kraja, prikaži prve i zadnje stranice s elipsama u sredini / EN: If near start or end, show first and last pages with ellipses in the middle
        $html .= $renderPageItem(2);
        $html .= $renderPageItem(3);
        $html .= $renderEllipsis();
        $html .= $renderPageItem($totalPages - 2);
        $html .= $renderPageItem($totalPages - 1);
      } else {
        // HR: Inače prikaži trenutnu stranicu s prethodnom i sljedećom te elipse sa strane / EN: Otherwise show current page with previous and next, and ellipses on both sides
        $html .= $renderEllipsis();
        $html .= $renderPageItem($page - 1);
        $html .= $renderPageItem($page);
        $html .= $renderPageItem($page + 1);
        $html .= $renderEllipsis();
      }
      $html .= $renderPageItem($totalPages);

      // Next
      $html .= '<li class="page-item '.($page >= $totalPages ? 'disabled' : '').'">';
      // HR: Dodaj gumb za sljedeću stranicu, onemogućen ako smo na zadnjoj / EN: Add next page button, disabled if on last page
      $html .= '<a class="page-link" href="'.$route.'?page='.min($totalPages, $page + 1).'&per_page='.$perPage.'&sort='.$sort.'&dir='.$dir.$searchParam.'">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';

    return $html;
  }
}

<?php

namespace App\Core;

use RuntimeException;

/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Ovo je centralna pomoćna klasa aplikacije.
 * Upravljanje basePath-om i generiranje URL-ova.
 * - setBasePath: postavlja osnovni path aplikacije
 * - baseHref: vraća <base href> za HTML (koristi se u layoutu)
 * - url: generira puni URL kombinirajući basePath i zadanu putanju
 *
 * ===========================
 *  English
 * ===========================
 * This is the central helper class for the application.
 * Manages basePath and generates URLs.
 * - setBasePath: sets the application's base path
 * - baseHref: returns <base href> for HTML (used in layout)
 * - url: generates a full URL by combining basePath and provided path
 */
class App
{
  private static string $basePath = '';

  // Postavlja osnovni (base) path aplikacije. / Sets the application's base path.
  public static function setBasePath(string $basePath): void
  {
    self::$basePath = rtrim($basePath, '/');
  }

  // Vraća osnovni <base href> za HTML (koristi se u layout-u). / Returns the base <base href> for HTML (used in layout).
  public static function baseHref(): string
  {
    $bp = self::$basePath;
    return $bp === '' ? '/' : $bp . '/';
  }

  // Generira puni URL spajanjem basePath-a i proslijeđene putanje. / Generates full URL by combining basePath and given path.
  public static function url(string $path = ''): string
  {
    $path = ltrim($path, '/');
    $bp = self::$basePath;
    return $bp === '' ? '/' . $path : $bp . '/' . $path;
  }

  // Generira URL na temelju imena rute i parametara. / Generates URL based on route name and parameters.
  public static function urlFor(string $routeName, array $params = []): string
  {
    global $router;
    foreach ($router->getRoutes() as $methodRoutes) {
      foreach ($methodRoutes as $route) {
        if (($route['name'] ?? null) === $routeName) {
          $url = $route['path'];
          foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', (string)$value, $url);
          }
          return self::url(ltrim($url, '/'));
        }
      }
    }
    throw new RuntimeException(_t('Naziv rute nije pronađen') . ": {$routeName}");
  }
}

<?php

namespace App\Core;

use RuntimeException;

/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Centralna pomoćna klasa aplikacije.
 * Upravljanje basePath-om i generiranje URL-ova.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * Central helper class of the application.
 * Manages basePath and generates URLs.
 */
class App
{
  /**
   * HR: Postavlja osnovni (base) path aplikacije.
   * EN: Sets the application's base path.
   *
   * @param string $basePath HR: Osnovni put aplikacije / EN: Application base path
   * @return void
   */
  public static function setBasePath(string $basePath): void
  {
    self::$basePath = rtrim($basePath, '/');
  }

  /**
   * HR: Vraća osnovni <base href> za HTML (koristi se u layoutu).
   * EN: Returns the base <base href> for HTML (used in layout).
   *
   * @return string HR: Osnovni URL za HTML <base> / EN: Base URL for HTML <base>
   */
  public static function baseHref(): string
  {
    $bp = self::$basePath;
    // HR: Uzimamo spremljeni basePath / EN: Retrieve stored basePath
    return $bp === '' ? '/' : $bp . '/';
  }

  /**
   * HR: Generira puni URL spajanjem basePath-a i zadane putanje.
   * EN: Generates full URL by combining basePath and the given path.
   *
   * @param string $path HR: Putanja unutar aplikacije / EN: Path within the application
   * @return string HR: Generirani puni URL / EN: Generated full URL
   */
  public static function url(string $path = ''): string
  {
    $path = ltrim($path, '/');
    // HR: Uklanjamo vodeću kosu crtu iz putanje / EN: Trim leading slash from path
    $bp = self::$basePath;
    return $bp === '' ? '/' . $path : $bp . '/' . $path;
  }

  /**
   * HR: Generira URL na temelju imena rute i parametara.
   * EN: Generates a URL based on route name and parameters.
   *
   * @param string $routeName HR: Naziv registrirane rute / EN: Registered route name
   * @param array $params HR: Parametri za zamjenu u ruti / EN: Parameters to replace in the route
   * @return string HR: Generirani URL / EN: Generated URL
   * @throws RuntimeException HR: Ako ruta nije pronađena / EN: If the route is not found
   */
  public static function urlFor(string $routeName, array $params = []): string
  {
    global $router;
    foreach ($router->getRoutes() as $methodRoutes) {
      // HR: Iteriramo kroz sve registrirane rute / EN: Iterate over all registered routes
      foreach ($methodRoutes as $route) {
        // HR: Provjeravamo podudara li se naziv rute / EN: Check if route name matches
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
    // HR: Ako ruta nije pronađena, bacamo iznimku / EN: If route not found, throw exception
  }

  private static string $basePath = '';
}

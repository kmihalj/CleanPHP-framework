<?php
/**
 * ===========================
 *  Hrvatski (Croatian)
 * ===========================
 * Router klasa upravlja rutiranjem HTTP zahtjeva.
 * - get/post: registriraju GET i POST rute.
 * - addRoute: dodaje rutu s pripadajućim patternom i middleware-om.
 * - compilePathToRegex: pretvara putanju s parametrima (npr. /user/{id}) u regex i popis parametara.
 * - dispatch: pronalazi odgovarajuću rutu, izvršava middleware i poziva odgovarajući kontroler i metodu.
 * - registerMiddleware: omogućava registraciju middleware funkcija (npr. 'auth').
 *
 * ===========================
 *  English
 * ===========================
 * The Router class manages routing of HTTP requests.
 * - get/post: register GET and POST routes.
 * - addRoute: adds a route with its pattern and middleware.
 * - compilePathToRegex: converts a path with parameters (e.g. /user/{id}) into regex and list of parameters.
 * - dispatch: finds a matching route, executes middleware, and calls the appropriate controller and method.
 * - registerMiddleware: allows registration of middleware functions (e.g. 'auth').
 */

namespace App\Core;

use PDO;

class Router
{
  private array $routes = ['GET' => [], 'POST' => []];
  // Pamti zadnju dodanu rutu radi točnog imenovanja. / Remember last added route for accurate naming.
  private ?array $lastAdded = null;
  private PDO $db;
  private string $basePath;
  private array $middlewareRegistry = [];
  private ?array $currentGroup = null;

  public function __construct(PDO $db, string $basePath = '')
  {
    $this->db = $db;
    $this->basePath = rtrim($basePath, '/');
    App::setBasePath($this->basePath);
  }

  // Registrira GET rutu. / Registers a GET route.
  public function get(string $path, string|array $action, array $middleware = []): self
  {
    return $this->addRoute('GET', $path, $action, $middleware);
  }

  // Registrira POST rutu. / Registers a POST route.
  public function post(string $path, string|array $action, array $middleware = []): self
  {
    return $this->addRoute('POST', $path, $action, $middleware);
  }

  // Registrira middleware funkciju pod imenom. / Registers a middleware function under a given name.
  public function registerMiddleware(string $name, callable $handler): void
  {
    $this->middlewareRegistry[$name] = $handler;
  }

  // Dodaje rutu u tablicu ruta s patternom i middleware-om. / Adds a route to the routes table with pattern and middleware.
  private function addRoute(string $method, string $path, string|array $action, array $middleware): self
  {
    // Ako postoji grupa, dodaj prefix i middleware. / If a group is active, add its prefix and middleware.
    if ($this->currentGroup) {
      $path = $this->currentGroup['prefix'] . $path;
      $middleware = array_merge($this->currentGroup['middleware'], $middleware);
    }

    $pattern = $this->compilePathToRegex($path);
    $this->routes[$method][] = [
      'path' => $path,
      'pattern' => $pattern['regex'],
      'params' => $pattern['params'],
      'action' => $action,
      'middleware' => $middleware,
      'name' => null
    ];
    // Zapamti zadnju dodanu rutu. / Remember the last added route.
    $lastIndex = array_key_last($this->routes[$method]);
    $this->lastAdded = ['method' => $method, 'index' => $lastIndex];
    return $this;
  }

  // Dodjeljuje ime zadnjoj registriranoj ruti. / Assigns a name to the last registered route.
  public function name(string $name): void
  {
    // Dodjeljuje ime upravo zadnje registriranoj ruti. / Assigns a name to the most recently registered route.
    if ($this->lastAdded === null) {
      return; // Nema rute za imenovati. / No route to name.
    }
    $m = $this->lastAdded['method'];
    $i = $this->lastAdded['index'];
    if (!isset($this->routes[$m][$i])) {
      return;
    }
    $this->routes[$m][$i]['name'] = $name;
  }

  // Pretvara path s {parametrima} u regex i vraća popis parametara. / Converts a path with {parameters} into regex and returns parameter list.
  private function compilePathToRegex(string $path): array
  {
    $paramNames = [];
    $regex = preg_replace_callback('#\{([^/]+)}#', function ($m) use (&$paramNames) {
      $paramNames[] = $m[1];
      return '(?P<' . $m[1] . '>[^/]+)';
    }, $path);
    $regex = '#^' . $regex . '$#';
    return ['regex' => $regex, 'params' => $paramNames];
  }

  // Pronalaženje odgovarajuće rute, izvršavanje middleware-a i kontrolera, te prosljeđivanje parametara.
  // Match the requested route, execute middleware and controller, and pass parameters.
  public function dispatch(string $uri, string $method): void
  {
    // Dohvati putanju iz URI-ja. / Extract the path from the URI.
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';

    // Ako aplikacija radi u poddirektoriju, ukloni basePath iz putanje. / If app runs in subdirectory, strip basePath from path.
    if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
      $path = substr($path, strlen($this->basePath)) ?: '/';
      if ($path === '') $path = '/';
    }

    // Učitaj sve registrirane rute za zadani HTTP metod (GET/POST). / Load all registered routes for the given HTTP method (GET/POST).
    $routes = $this->routes[$method] ?? [];

    foreach ($routes as $route) {
      // Provjeri poklapa li se trenutna putanja s regex patternom rute. / Check if current path matches the route's regex pattern.
      if (preg_match($route['pattern'], $path, $matches)) {

        // Odredi kontroler i metodu: podržava array [Controller::class, method] ili string "Controller@method".
        // Resolve controller and method: supports array [Controller::class, method] or string "Controller@method".
        if (is_array($route['action'])) {
          // [Controller::class, 'method']
          [$controllerClass, $methodName] = $route['action'];
        } else {
          // "Controller@method"
          [$controllerName, $methodName] = explode('@', $route['action']);
          $controllerClass = 'App\\Controllers\\' . $controllerName;
        }

        // Ako kontroler ne postoji, vrati 500 error. / If controller class does not exist, return 500 error.
        if (!class_exists($controllerClass)) {
          http_response_code(500);
          echo _t("Controller {$controllerClass} nije pronađen.");
          return;
        }
        $controller = new $controllerClass($this->db);

        // Ekstraktiraj parametre iz regex matcha i proslijedi ih metodi kontrolera. / Extract parameters from regex match and pass them to controller method.
        $params = [];
        foreach ($route['params'] as $p) {
          if (isset($matches[$p])) {
            $params[$p] = $matches[$p];
          }
        }

        // Pokreni sve middleware-e definirane za ovu rutu. Ako bilo koji vrati false, prekini obradu.
        // Run all middleware attached to this route. If any returns false, stop execution.
        foreach ($route['middleware'] as $mwName) {
          if (!isset($this->middlewareRegistry[$mwName])) continue;
          $ok = call_user_func($this->middlewareRegistry[$mwName], $path, $method);
          if ($ok === false) return;
        }

        // Provjera session zastavice za reset lozinke / Check the password_reset session flag
        $passwordResetFlag = $_SESSION['password_reset'] ?? 0;
        if (!empty($_SESSION['user_id']) && (int)$passwordResetFlag === 1) {
          // Dozvoli samo change-password, logout i promjenu jezika / Allow only change-password, logout, and language switch
          if (
            !in_array($path, ['/change-password', '/logout'], true)
            && !preg_match('#^/lang/[a-z]{2}$#', $path)
          ) {
            flash_set('error', _t('Prijavljeni ste s privremenom lozinkom. Molimo promijenite lozinku prije nastavka.'));
            header('Location: ' . App::url('change-password'));
            exit;
          }
        }

        // Pozovi metodu kontrolera s prikupljenim parametrima. / Call the controller method with the collected parameters.
        call_user_func_array([$controller, $methodName], $params);
        return;
      }
    }

    http_response_code(404);
    echo _t("404 - Ruta nije pronađena.");
  }

  // Vraća sve registrirane rute. / Returns all registered routes.
  public function getRoutes(): array
  {
    return $this->routes;
  }

  /**
   * ===========================
   *  Hrvatski (Croatian)
   * ===========================
   * Definira grupu ruta sa zajedničkim prefixom i middleware-om.
   * Sve rute unutar callback funkcije dobit će prefix i middleware grupe.
   *
   * ===========================
   *  English
   * ===========================
   * Defines a group of routes with a common prefix and middleware.
   * All routes inside the callback will inherit the group's prefix and middleware.
   */
  public function group(string $prefix, array $middleware, callable $callback): void
  {
    $previousGroup = $this->currentGroup;
    $this->currentGroup = [
      'prefix' => $prefix,
      'middleware' => $middleware,
    ];
    $callback($this);
    $this->currentGroup = $previousGroup; // restore previous group context
  }
}

<?php
/**
 * ===========================================================
 *  Hrvatski (Croatian)
 * ===========================================================
 * Router klasa upravlja rutiranjem HTTP zahtjeva.
 * Omogućuje registraciju ruta, dodjelu middleware-a,
 * i izvršavanje odgovarajućih kontrolera.
 *
 * ===========================================================
 *  English
 * ===========================================================
 * The Router class manages routing of HTTP requests.
 * Allows registering routes, assigning middleware,
 * and executing the appropriate controllers.
 */

namespace App\Core;

use RuntimeException;

class Router
{
  private array $routes = ['GET' => [], 'POST' => []];
  // Pamti zadnju dodanu rutu radi točnog imenovanja. / Remember last added route for accurate naming.
  private ?array $lastAdded = null;
  private string $basePath;
  private array $middlewareRegistry = [];
  private ?array $currentGroup = null;

  /**
   * HR: Konstruktor - postavlja basePath i sprema ga u App.
   * EN: Constructor - sets basePath and stores it in App.
   *
   * @param string $basePath HR: Osnovni put aplikacije / EN: Application base path
   * @return void
   */
  public function __construct(string $basePath = '')
  {
    $this->basePath = rtrim($basePath, '/');
    App::setBasePath($this->basePath);
  }

  /**
   * HR: Registrira GET rutu.
   * EN: Registers a GET route.
   *
   * @param string $path HR: Putanja rute / EN: Route path
   * @param string|array $action HR: Akcija (Controller@method ili [Controller::class, 'method']) / EN: Action (Controller@method or [Controller::class, 'method'])
   * @param array $middleware HR: Middleware koji se primjenjuje na rutu / EN: Middleware applied to the route
   * @return self
   */
  public function get(string $path, string|array $action, array $middleware = []): self
  {
    return $this->addRoute('GET', $path, $action, $middleware);
  }

  /**
   * HR: Registrira POST rutu.
   * EN: Registers a POST route.
   *
   * @param string $path HR: Putanja rute / EN: Route path
   * @param string|array $action HR: Akcija (Controller@method ili [Controller::class, 'method']) / EN: Action (Controller@method or [Controller::class, 'method'])
   * @param array $middleware HR: Middleware koji se primjenjuje na rutu / EN: Middleware applied to the route
   * @return self
   */
  public function post(string $path, string|array $action, array $middleware = []): self
  {
    return $this->addRoute('POST', $path, $action, $middleware);
  }

  /**
   * HR: Registrira middleware pod zadanim imenom.
   * EN: Registers a middleware under a given name.
   *
   * @param string $name HR: Naziv middleware-a / EN: Middleware name
   * @param callable $handler HR: Funkcija koja implementira middleware / EN: Function that implements middleware
   * @return void
   */
  public function registerMiddleware(string $name, callable $handler): void
  {
    // HR: Wrap za 'auth' middleware - sprema intended_url i preusmjerava na login
    // EN: Wrap for 'auth' middleware - stores intended_url and redirects to login
    if ($name === 'auth') {
      $this->middlewareRegistry[$name] = function ($path, $method) use ($handler) {
        $result = $handler($path, $method);
        if ($result === false) {
          // User is not authenticated. Store intended_url and redirect to login.
          if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
          }
          $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? App::url();
          $_SESSION['intended_middleware'] = 'auth';
          header('Location: ' . App::urlFor('login.form'));
          exit;
        }
        return $result;
      };
      // HR: Wrap za 'admin' middleware - sprema intended_url ili preusmjerava s greškom
      // EN: Wrap for 'admin' middleware - stores intended_url or redirects with error
    } elseif ($name === 'admin') {
      $this->middlewareRegistry[$name] = function ($path, $method) use ($handler) {
        $result = $handler($path, $method);
        if ($result === false) {
          // User is not authenticated or not admin.
          if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
          }
          if (empty($_SESSION['user'])) {
            // User not authenticated: store intended_url and redirect to login.
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? App::url();
            $_SESSION['intended_middleware'] = 'admin';
            header('Location: ' . App::urlFor('login.form'));
          } else {
            // User is authenticated but not admin: clear intended_url and redirect forbidden.
            unset($_SESSION['intended_url'], $_SESSION['intended_middleware']); // clear any stored redirect
            flash_set('error', _t("Nemate dozvolu za pristup željenoj stranici."));
            header('Location: ' . App::urlFor('admin.forbidden'));
          }
          exit;
        } elseif ($result === 'not_admin') {
          // User is authenticated but not admin. Do not override intended_url.
          if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
          }
          unset($_SESSION['intended_url'], $_SESSION['intended_middleware']); // clear any stored redirect
          flash_set('error', _t("Nemate dozvolu za pristup željenoj stranici."));
          header('Location: ' . App::urlFor('admin.forbidden'));
          exit;
        }
        return $result;
      };
    } else {
      $this->middlewareRegistry[$name] = $handler;
    }
  }

  // HR: Dodaje rutu u tablicu ruta s patternom i middleware-om
  // EN: Adds a route to the routes table with pattern and middleware
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

  /**
   * HR: Dodjeljuje ime zadnjoj registriranoj ruti.
   * EN: Assigns a name to the last registered route.
   *
   * @param string $name HR: Naziv rute / EN: Route name
   * @return void
   */
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

  // HR: Pretvara path s {parametrima} u regex i vraća popis parametara
  // EN: Converts path with {parameters} into regex and returns parameter list
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

  /**
   * HR: Pronalazi odgovarajuću rutu, izvršava middleware i kontroler.
   * EN: Finds a matching route, executes middleware and controller.
   *
   * @param string $uri HR: URI zahtjeva / EN: Request URI
   * @param string $method HR: HTTP metoda (GET, POST, ...) / EN: HTTP method (GET, POST, ...)
   * @return void
   * @throws RuntimeException HR: Ako kontroler nije pronađen ili CSRF token nije valjan / EN: If controller not found or CSRF token invalid
   */
  public function dispatch(string $uri, string $method): void
  {
    // HR: Dohvati path iz URI-ja / EN: Extract path from URI
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';

    // HR: Ako app radi u poddirektoriju, ukloni basePath / EN: If app runs in subdirectory, strip basePath
    if ($this->basePath !== '' && str_starts_with($path, $this->basePath)) {
      $path = substr($path, strlen($this->basePath)) ?: '/';
      if ($path === '') $path = '/';
    }

    // HR: Provjera privremene lozinke i dozvoljenih ruta / EN: Check for temporary password and allowed routes
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    if (isset($_SESSION['user']['privremenaLozinka'])) {
      $allowedRoutes = [
        'passwordChange.form',
        'passwordChange.submit',
        'lang.switch',
        'logout.submit'
      ];
      $routeAllowed = false;
      foreach ($allowedRoutes as $allowedRouteName) {
        foreach ($this->routes[$method] ?? [] as $route) {
          if ($route['name'] === $allowedRouteName) {
            if (preg_match($route['pattern'], $path)) {
              $routeAllowed = true;
              break 2;
            }
          }
        }
      }
      if (!$routeAllowed) {
        flash_set('error',_t("Morate promijeniti privremenu lozinku prije nastavka."));
        header('Location: ' . App::urlFor('passwordChange.form'));
        exit;
      }
    }

    // HR: Iteracija kroz rute i match po regexu / EN: Iterate through routes and match by regex
    $routes = $this->routes[$method] ?? [];

    foreach ($routes as $route) {
      if (preg_match($route['pattern'], $path, $matches)) {

        // HR: Resolve kontroler i metodu / EN: Resolve controller and method
        if (is_array($route['action'])) {
          // [Controller::class, 'method']
          [$controllerClass, $methodName] = $route['action'];
        } else {
          // "Controller@method"
          [$controllerName, $methodName] = explode('@', $route['action']);
          $controllerClass = 'App\\Controllers\\' . $controllerName;
        }

        if (!class_exists($controllerClass)) {
          http_response_code(500);
          echo _t("Controller {$controllerClass} nije pronađen.");
          return;
        }
        $controller = new $controllerClass();

        // HR: Ekstraktiraj parametre i proslijedi metodi / EN: Extract parameters and pass to method
        $params = [];
        foreach ($route['params'] as $p) {
          if (isset($matches[$p])) {
            $params[$p] = $matches[$p];
          }
        }

        // HR: Pokreni middleware za rutu / EN: Run middleware for the route
        foreach ($route['middleware'] as $mwName) {
          if (!isset($this->middlewareRegistry[$mwName])) continue;
          $ok = call_user_func($this->middlewareRegistry[$mwName], $path, $method);
          if ($ok === false) return;
        }

        // HR: CSRF provjera za POST/PUT/DELETE / EN: CSRF validation for POST/PUT/DELETE
        if (in_array($method, ['POST', 'PUT', 'DELETE'], true) && !Csrf::validate()) {
          http_response_code(403);
          echo _t("CSRF token nije valjan.");
          return;
        }

        // HR: Pozovi kontroler metodu s parametrima / EN: Call controller method with parameters
        call_user_func_array([$controller, $methodName], $params);
        return;
      }
    }

    http_response_code(404);
    echo _t("404 - Ruta nije pronađena.");
  }

  /**
   * HR: Vraća sve registrirane rute.
   * EN: Returns all registered routes.
   *
   * @return array HR: Polje svih ruta / EN: Array of all routes
   */
  public function getRoutes(): array
  {
    return $this->routes;
  }

  /**
   * HR: Definira grupu ruta sa zajedničkim prefixom i middleware-om.
   * EN: Defines a group of routes with a common prefix and middleware.
   *
   * @param string $prefix HR: Prefix putanje za grupu ruta / EN: Path prefix for the route group
   * @param array $middleware HR: Middleware primijenjen na grupu / EN: Middleware applied to the group
   * @param callable $callback HR: Callback funkcija za definiranje ruta / EN: Callback function to define routes
   * @return void
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

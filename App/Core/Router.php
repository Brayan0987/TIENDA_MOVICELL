<?php
// App/Core/Router.php
namespace App\Core;

final class Router {
  private array $routes = ['GET' => [], 'POST' => []];

  public function get(string $path, string $target): void {
    $this->routes['GET'][$this->norm($path)] = $target;
  }

  public function post(string $path, string $target): void {
    $this->routes['POST'][$this->norm($path)] = $target;
  }

  public function dispatch(string $method, string $uri): void {
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';

    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($base !== '' && strpos($path, $base) === 0) {
      $path = substr($path, strlen($base));
    }

    if ($path === '') $path = '/';
    $path = $this->norm($path);

    // IMPORTANTE: NO emitir salida aquí para no romper headers de redirección [3]

    if (!isset($this->routes[$method][$path])) {
      http_response_code(404);
      echo "404: Ruta no registrada: $path";
      return;
    }

    [$controller, $action] = explode('@', $this->routes[$method][$path]);
    $class = "App\\Controllers\\$controller";

    if (!class_exists($class)) {
      http_response_code(500);
      echo "Controller $class no encontrado";
      return;
    }

    $instance = new $class;

    if (!method_exists($instance, $action)) {
      http_response_code(500);
      echo "Método $action no encontrado en $class";
      return;
    }

    $instance->$action();
  }

  private function norm(string $path): string {
    return $path === '' ? '/' : rtrim($path, '/');
  }
}

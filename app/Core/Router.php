<?php
namespace App\Core;

/**
 * Clean URL Router with Parameter Regex Matching & Middleware Execution
 */
class Router
{
    private static array $routes = [];

    public static function get(string $pattern, string|array|callable $handler, array $middleware = []): void
    {
        self::addRoute('GET', $pattern, $handler, $middleware);
    }

    public static function post(string $pattern, string|array|callable $handler, array $middleware = []): void
    {
        self::addRoute('POST', $pattern, $handler, $middleware);
    }

    public static function put(string $pattern, string|array|callable $handler, array $middleware = []): void
    {
        self::addRoute('PUT', $pattern, $handler, $middleware);
    }

    public static function delete(string $pattern, string|array|callable $handler, array $middleware = []): void
    {
        self::addRoute('DELETE', $pattern, $handler, $middleware);
    }

    private static function addRoute(string $method, string $pattern, string|array|callable $handler, array $middleware): void
    {
        // Normalize pattern
        $pattern = '/' . trim($pattern, '/');
        if ($pattern === '') {
            $pattern = '/';
        }

        // Convert {param} into regex capture group
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        self::$routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public static function dispatch(Request $request): void
    {
        $requestMethod = $request->getMethod();
        $requestUri = $request->getUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if (preg_match($route['regex'], $requestUri, $matches)) {
                // Extract named params
                $params = array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);

                // Run Middleware Pipeline
                foreach ($route['middleware'] as $mw) {
                    $middlewareClass = is_string($mw) ? $mw : null;
                    if ($middlewareClass && class_exists($middlewareClass)) {
                        $mwInstance = new $middlewareClass();
                        if (method_exists($mwInstance, 'handle')) {
                            $mwInstance->handle($request);
                        }
                    } elseif (is_callable($mw)) {
                        $mw($request);
                    }
                }

                // Invoke Route Handler
                $handler = $route['handler'];
                
                // 1. String handler: 'LandingController@index'
                if (is_string($handler) && str_contains($handler, '@')) {
                    [$ctrl, $action] = explode('@', $handler, 2);
                    $fullControllerClass = str_starts_with($ctrl, 'App\\') ? $ctrl : "App\\Controllers\\{$ctrl}";
                    
                    if (class_exists($fullControllerClass)) {
                        $controller = new $fullControllerClass();
                        if (method_exists($controller, $action)) {
                            call_user_func_array([$controller, $action], array_values($params));
                            return;
                        }
                    }
                }

                // 2. Direct Callable / Closure
                if (is_callable($handler)) {
                    call_user_func_array($handler, array_values($params));
                    return;
                }

                // 3. Array handler: [Controller::class, 'method']
                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $method] = $handler;
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $method)) {
                            call_user_func_array([$controller, $method], array_values($params));
                            return;
                        }
                    }
                }

                Response::abort(500, "Invalid route handler configured for [{$requestUri}]");
                return;
            }
        }

        // No route matched
        Response::abort(404, "Page Not Found: The requested URL was not found on this server.");
    }
}

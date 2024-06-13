<?php

namespace ngphp\http;

use ngphp\http\attributes\Connect;
use ngphp\http\attributes\Delete;
use ngphp\http\attributes\Get;
use ngphp\http\attributes\Head;
use ngphp\http\attributes\Options;
use ngphp\http\attributes\Patch;
use ngphp\http\attributes\Post;
use ngphp\http\attributes\Pri;
use ngphp\http\attributes\Put;
use ngphp\http\attributes\RouteGroup;
use ngphp\http\attributes\Trace;
use ReflectionClass;

class Router
{
    private $routes = [];
    private $prefix = '';
    private $baseUri = '';
    private $allowedOrigins = [];

    public function __construct($config = [])
    {
        if (isset($config['baseUri'])) {
            $this->baseUri = $config['baseUri'];
        }

        if (isset($config['allowedOrigins'])) {
            $this->allowedOrigins = $config['allowedOrigins'];
        }
    }

    public function registerRoutesFromController($controller)
    {
        try {
            $reflection = new ReflectionClass($controller);
        } catch (\ReflectionException $e) {
            echo 'ReflectionException: ' . $e->getMessage() . "\n";
            die('ReflectionException: ' . $e->getMessage());
        }

        // Sınıf üzerindeki RouteGroup özniteliğini kontrol edin
        $routeGroupAttribute = $reflection->getAttributes(RouteGroup::class);
        if (!empty($routeGroupAttribute)) {
            $instance = $routeGroupAttribute[0]->newInstance();
            $previousPrefix = $this->prefix;
            $this->prefix .= $instance->prefix;
        }

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();
                $httpMethod = $this->getHttpMethodFromAttribute($attribute->getName());
                if ($httpMethod) {
                    $this->addRoute($httpMethod, $instance->path, $controller, $method->getName());
                }
            }
        }

        if (!empty($routeGroupAttribute)) {
            $this->prefix = $previousPrefix;
        }
    }

    private function getHttpMethodFromAttribute($attributeName)
    {
        switch ($attributeName) {
            case Get::class:
                return 'GET';
            case Post::class:
                return 'POST';
            case Put::class:
                return 'PUT';
            case Delete::class:
                return 'DELETE';
            case Patch::class:
                return 'PATCH';
            case Options::class:
                return 'OPTIONS';
            case Head::class:
                return 'HEAD';
            case Trace::class:
                return 'TRACE';
            case Connect::class:
                return 'CONNECT';
            case Pri::class:
                return 'PRI';
            // Diğer HTTP metodları için de burada kontrol ekleyebilirsiniz.
            default:
                return null;
        }
    }

    public function get($path, $resource, $method)
    {
        $this->addRoute('GET', $path, $resource, $method);
    }

    public function post($path, $resource, $method)
    {
        $this->addRoute('POST', $path, $resource, $method);
    }

    public function put($path, $resource, $method)
    {
        $this->addRoute('PUT', $path, $resource, $method);
    }

    public function delete($path, $resource, $method)
    {
        $this->addRoute('DELETE', $path, $resource, $method);
    }

    public function patch($path, $resource, $method)
    {
        $this->addRoute('PATCH', $path, $resource, $method);
    }

    public function options($path, $resource, $method)
    {
        $this->addRoute('OPTIONS', $path, $resource, $method);
    }

    public function head($path, $resource, $method)
    {
        $this->addRoute('HEAD', $path, $resource, $method);
    }

    public function trace($path, $resource, $method)
    {
        $this->addRoute('TRACE', $path, $resource, $method);
    }

    public function connect($path, $resource, $method)
    {
        $this->addRoute('CONNECT', $path, $resource, $method);
    }

    public function pri($path, $resource, $method)
    {
        $this->addRoute('PRI', $path, $resource, $method);
    }

    private function addRoute($method, $path, $resource, $resourceMethod)
    {
        $fullPath = '/' . trim($this->prefix . '/' . trim($path, '/'), '/'); // Tam yolu oluşturun

        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $this->routes[$method][$fullPath] = ['resource' => $resource, 'method' => $resourceMethod];
    }

    public function dispatch($uri, $request, $response)
    {
        try {
            $parsedUri = parse_url($uri, PHP_URL_PATH); // URI'yı tam olarak çözümle
            if ($this->baseUri && strpos($parsedUri, $this->baseUri) === 0) {
                $parsedUri = substr($parsedUri, strlen($this->baseUri));
            }

            // CORS ayarlarını kontrol edin
            if (!empty($this->allowedOrigins)) {
                $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
                $requestMethod = $_SERVER['REQUEST_METHOD'];
                
                if ($this->isOriginAllowed($origin, $requestMethod)) {
                    header('Access-Control-Allow-Origin: ' . $origin);
                    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, TRACE, CONNECT, PRI');
                    header('Access-Control-Allow-Headers: Content-Type, Authorization');
                } else {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden', 'message' => 'Origin or method not allowed.']);
                    return;
                }
            }

            echo "Parsed URI: " . $parsedUri . "\n"; // URI'yi loglayın
            echo "Routes: \n";
            var_dump($this->routes); // Rotayı yazdırın
            if (!$this->handleRouting($parsedUri, $request, $response)) {
                http_response_code(404);
                echo json_encode(['error' => 'Not Found', 'message' => 'No route matches the provided URI.']);
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage() . "\n";
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => $exception->getMessage()]);
        }
    }

    private function isOriginAllowed($origin, $method)
    {
        foreach ($this->allowedOrigins as $allowedOrigin => $methods) {
            if (($allowedOrigin === '*' || $allowedOrigin === $origin) && in_array($method, $methods)) {
                return true;
            }
        }
        return false;
    }

    private function handleRouting($uri, $request, $response)
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        echo "Handling routing for URI: $uri with method: $requestMethod\n";
        $matched = false;

        if (isset($this->routes[$requestMethod])) {
            foreach ($this->routes[$requestMethod] as $path => $details) {
                echo "Checking route: $path\n";
                $pattern = "@^" . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $path) . "$@D";
                echo "Pattern: " . $pattern . "\n";
                if (preg_match($pattern, $uri, $matches)) {
                    echo "Match found: \n";
                    print_r($matches);
                    array_shift($matches);
                    $controllerName = $details['resource'];
                    $methodName = $details['method'];
                    $controller = new $controllerName;
                    call_user_func_array([$controller, $methodName], array_merge([$request, $response], $matches));
                    $matched = true;
                    return true;
                }
            }
        }

        if (!$matched && isset($this->routes[$requestMethod][$uri])) {
            $controllerName = $this->routes[$requestMethod][$uri]['resource'];
            $methodName = $this->routes[$requestMethod][$uri]['method'];
            $controller = new $controllerName;
            $controller->$methodName($request, $response);
            return true;
        }

        echo "No route matched for URI: $uri\n";
        return false;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
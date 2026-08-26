<?php
// helpers/Router.php
// Ubicación: C:\xampp\htdocs\proyecto\helpers\Router.php

class Router
{
    private $routes = [];
    private $notFound = '';

    /**
     * Agregar una ruta
     * @param string $route La ruta (ej: /auth/login)
     * @param string $handler Controlador@metodo (ej: AuthController@login)
     */
    public function add($route, $handler)
    {
        // Convertir parámetros :num y :any a regex
        $route = preg_quote($route, '/');
        $route = str_replace('\:num', '([0-9]+)', $route);
        $route = str_replace('\:any', '([a-zA-Z0-9\-_]+)', $route);
        $route = '/^' . $route . '$/';
        
        $this->routes[] = [
            'pattern' => $route,
            'handler' => $handler
        ];
    }

    /**
     * Establecer ruta para 404
     */
    public function setNotFound($route)
    {
        $this->notFound = $route;
    }

    /**
     * Despachar la URL al controlador correspondiente
     */
    public function dispatch($url)
    {
        // Limpiar URL
        $url = trim($url, '/');
        if (empty($url)) {
            $url = 'auth/login';
        }

        // Eliminar parámetros GET
        $url = strtok($url, '?');

        // Buscar en rutas definidas
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $url, $matches)) {
                array_shift($matches);
                return $this->executeHandler($route['handler'], $matches);
            }
        }

        // Si no se encontró ruta, intentar con /controller/action
        $parts = explode('/', $url);
        $controllerName = isset($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'AuthController';
        $methodName = isset($parts[1]) ? $parts[1] : 'login';
        $params = array_slice($parts, 2);

        return $this->executeHandler($controllerName . '@' . $methodName, $params);
    }

    /**
     * Ejecutar el handler
     */
    private function executeHandler($handler, $params = [])
    {
        $parts = explode('@', $handler);
        
        if (count($parts) !== 2) {
            $this->show404("Formato de handler inválido: $handler");
            return;
        }
        
        $controllerName = $parts[0];
        $methodName = $parts[1];
        
        // Construir ruta del controlador
        $controllerFile = __DIR__ . '/../controller/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            $this->show404("Controlador no encontrado: $controllerName (Archivo: $controllerFile)");
            return;
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            $this->show404("Clase no encontrada: $controllerName");
            return;
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $methodName)) {
            $this->show404("Método no encontrado: $methodName en $controllerName");
            return;
        }
        
        return call_user_func_array([$controller, $methodName], $params);
    }

    /**
     * Mostrar error 404
     */
    private function show404($message = '')
    {
        header("HTTP/1.0 404 Not Found");
        echo '<h1>404 - Página no encontrada</h1>';
        if (!empty($message)) {
            echo "<p><strong>Error:</strong> $message</p>";
        }
        echo '<p><a href="/proyecto/auth/login">Ir al inicio</a></p>';
        exit;
    }

    /**
     * Obtener todas las rutas registradas (para depuración)
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
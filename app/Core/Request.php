<?php
namespace App\Core;

/**
 * HTTP Request encapsulation
 */
class Request
{
    private string $method;
    private string $uri;
    private array $queryParams;
    private array $postData;
    private array $files;
    private array $server;
    private ?array $jsonData = null;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $this->parseUri();
        $this->queryParams = Security::cleanInput($_GET);
        $this->postData = Security::cleanInput($_POST);
        $this->files = $_FILES;
        $this->server = $_SERVER;

        // Parse JSON payload if Content-Type is application/json
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $input = file_get_contents('php://input');
            $this->jsonData = json_decode($input, true) ?: [];
        }
    }

    private function parseUri(): string
    {
        $config = require __DIR__ . '/../Config/app.php';
        $basePath = $config['base_path'] ?? '';
        
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Strip query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove base path from URI if present
        if (!empty($basePath) && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isAjax(): bool
    {
        return !empty($this->server['HTTP_X_REQUESTED_WITH']) && 
               strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if ($this->jsonData !== null && array_key_exists($key, $this->jsonData)) {
            return $this->jsonData[$key];
        }
        if (array_key_exists($key, $this->postData)) {
            return $this->postData[$key];
        }
        if (array_key_exists($key, $this->queryParams)) {
            return $this->queryParams[$key];
        }
        return $default;
    }

    public function all(): array
    {
        if ($this->jsonData !== null) {
            return array_merge($this->queryParams, $this->jsonData);
        }
        return array_merge($this->queryParams, $this->postData);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
}

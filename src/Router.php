<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use function array_reverse;
use function array_values;
use function count;
use function explode;
use function parse_url;
use function str_replace;
use function strtoupper;
use function substr;
use function trim;

use const PHP_URL_PATH;

class Router
{
    private array $routes = [];

    public function get(string $route, callable $fn): void
    {
        if (!isset($this->routes['GET'])) {
            $this->routes['GET'] = [];
        }

        $this->routes['GET'][$route] = $fn;
    }

    public function post(string $route, callable $fn): void
    {
        if (!isset($this->routes['POST'])) {
            $this->routes['POST'] = [];
        }

        $this->routes['POST'][$route] = $fn;
    }

    /**
     * We need to compare wildcard patterns
     * Example: /titles/3/addEntry vs. /titles/{id}/addEntry
     */
    public function compareUrl(string $url, string $pattern): bool
    {
        $urlArray = $this->getUrlArray($url);
        $patternArray = $this->getUrlArray($pattern);

        if (count($patternArray) !== count($urlArray)) {
            return false;
        }

        foreach ($urlArray as $key => $value) {
            if ($urlArray[$key] === $patternArray[$key]) {
                continue;
            }

            if (!$this->hasBrackets($patternArray[$key])) {
                return false;
            }
        }
        return true;
    }

    public function getWildcards(string $url, string $pattern): array
    {
        $urlArray = $this->getUrlArray($url);
        $patternArray = $this->getUrlArray($pattern);

        $toReturn = [];
        foreach ($urlArray as $key => $value) {
            if ($urlArray[$key] === $patternArray[$key]) {
                continue;
            }

            if ($this->hasBrackets($patternArray[$key])) {
                $variableName = $this->removeBrackets($patternArray[$key]);
                $toReturn[$variableName] = $urlArray[$key];
            }
        }
        return $toReturn;
    }

    private function getUrlArray(string $url): array
    {
        $url = trim(parse_url($url ?? '/', PHP_URL_PATH), '/');
        return explode('/', $url);
    }

    private function hasBrackets(string $string): bool
    {
        return substr($string, -1) === '}' && $string[0] === '{';
    }

    private function removeBrackets(string $string): string
    {
        return str_replace(['{', '}'], '', $string);
    }

    public function run(string $requestType, $url): void
    {
        $requestType = strtoupper($requestType);

        if (!isset($this->routes[$requestType])) {
            return;
        }

        $routes = array_reverse($this->routes[$requestType]);

        foreach ($routes as $route => $fn) {
            if ($this->compareUrl($url, $route)) {
                $variables = $this->getWildcards($url, $route);
                $fn(...array_values($variables));
                return;
            }
        }
    }
}

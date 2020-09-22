<?php

namespace Psf1\RouterMatcher;

/**
 * Class RouterMatcher.
 *
 * @package Psf1\RouterMatcher
 */
class RouterMatcher {

    /**
     * Internal router collection.
     *
     * @var array
     */
    protected $routeCollection;

    /**
     * Valid route patterns.
     *
     * @var array
     */
    protected $routes;

    /**
     * Analyzed route paths.
     *
     * @var array
     */
    protected $routeMatchCache;

    /**
     * RouterMatcher constructor.
     */
    public function __construct() {
        $this->clean();
    }

    /**
     * Clear route cache.
     */
    public function cleanCache() {
        $this->routeMatchCache = [];
    }

    /**
     * Clear all settings.
     */
    public function clean() {
        $this->routeCollection = [];
        $this->routes = [];
        $this->cleanCache();
    }

    /**
     * Add a pattern to the router.
     *
     * @param string $route
     *   The path pattern.
     */
    public function addRoute($route) {
        if (substr($route, 0, 1) == '/') {
            $route = substr($route, 1);
        }
        // Add to valid routes.
        $this->routes[$route] = $route;
        // Build parser helper array.
        $route = explode('/', $route);
        $levels = count($route);
        $parent = &$this->routeCollection;
        for ($i = 0; $i < $levels; ++$i) {
            if (!isset($parent[$route[$i]])) {
                $parent[$route[$i]] = [];
            }
            $parent = &$parent[$route[$i]];
        }
    }

    /**
     * Switch tool. Compare a path with a route pattern.
     *
     * @param string $route
     *   Route pattern.
     * @param string $path
     *   Path.
     *
     * @return bool
     *   TRUE if match.
     */
    public function isMatch($route, $path) {
        if (substr($route, 0, 1) == '/') {
            $route = substr($route, 1);
        }
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }
        if (!isset($this->routes[$route])) {
            return FALSE;
        }
        $route = explode('/', $route);
        $path = explode('/', $path);

        if (count($route) != count($path)) {
            return FALSE;
        }

        $levels = count($path);
        for ($i = 0; $i < $levels; ++$i) {
            if ($route[$i] != $path[$i]) {
                if (substr($route[$i], 0, 1) != '{') {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    /**
     * Parse a path in the routes collection.
     *
     * @param string $path
     *   The path to analyze.
     *
     * @return array|bool
     *   Array with path parameters or FALSE if is a not match any route.
     */
    public function parseRoute($path) {
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }
        if (!isset($this->routeMatchCache[$path])) {
            $parts = explode('/', $path);
            $levels = count($parts);
            $routeMap = $this->routeCollection;
            $values = [];
            for ($i = 0; $i < $levels; ++$i) {
                if (isset($routeMap[$parts[$i]])) {
                    $routeMap = $routeMap[$parts[$i]];
                }
                else {
                    $matched = FALSE;
                    foreach ($routeMap as $routeKey => $subRoute) {
                        if (substr($routeKey, 0, 1) == '{') {
                            $key = str_replace(['{', '}'], '', $routeKey);
                            $values[$key] = $parts[$i];
                            $routeMap = $routeMap[$routeKey];
                            $matched = TRUE;
                            break;
                        }
                    }
                    if (!$matched) {
                        $this->routeMatchCache[$path] = FALSE;
                        break;
                    }
                }
            }
            $this->routeMatchCache[$path] = $values;
        }
        return $this->routeMatchCache[$path];
    }

}

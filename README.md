# router-matcher
URL router patterns tool

This library allows to create local URL responses, for example, to use in unit tests.

Install:

`composer require 'psf1/router-matcher'`

How to use:

```php
<?php

use Psf1\RouterMatcher\RouterMatcher;

class myServer {
    
  /**
   * Router matcher.
   *
   * @var \Psf1\RouterMatcher\RouterMatcher
   */
  protected $routerMatcher;

  /**
   * Creates a myServer instance.
   */
  public function __construct() {
    // Create the matcher.
    $this->routerMatcher = new RouterMatcher();
    // Add new routes. If we need a subfolder where the second
    // can have dynamic values, it must be declared after the 
    // fixed one.
    // The same with other levels.
    $this->routerMatcher->addRoute('/example');
    // To declare a dynamic value you must enclose it with '{}'.
    $this->routerMatcher->addRoute('/entity/level2/{session_key}');
    $this->routerMatcher->addRoute('/entity/{session_key}');
    $this->routerMatcher->addRoute('/other-entity/list');
    $this->routerMatcher->addRoute('/other-entity/{id}/childs');
  }

  /**
   * Helper function to simulate send a request with JSON data.
   *
   * @param string $method
   *   HTTP method.
   * @param string $url
   *   Request URL.
   * @param array $request_data
   *   Request data.
   *
   * @return array
   *   Response data.
   */
   public function request($method, $url, array $request_data) {
     $response = [];
     $path = parse_url($url, PHP_URL_PATH);
     switch ($path) {
       case $this->routerMatcher->isMatch('/example', $path):
         switch ($method) {
           case 'POST':
             $response = [
                'http_code' => 200,
                'data' => '/example POST response',
             ];
           break;

           case 'GET':
             $response = [
               'http_code' => 200,
               'data' => '/example GET response',
             ];
           break;
        }
        break;
    
       case $this->routerMatcher->isMatch('/entity/{session_key}', $path):
         // Get URL parameters.
         $parameters = $this->routerMatcher->parseRoute($path);
         $response = [
            'http_code' => 200,
            'data' => '/entity/{session_key} response',
            'parameters' => $parameters,
         ];
       break;
       
       case $this->routerMatcher->isMatch('/entity/level2/{session_key}', $path):
       case $this->routerMatcher->isMatch('/other-entity/list', $path):
       case $this->routerMatcher->isMatch('/other-entity/{id}/childs', $path):
         // Get URL parameters.
         $parameters = $this->routerMatcher->parseRoute($path);
         $response = [
            'http_code' => 200,
            'data' => 'Other response',
            'parameters' => $parameters,
         ];
       break;

       default:
         $response = [
           'http_code' => 404,
           'data' => 'URL not found.'
         ];
       break;
     }
     return $response;
   }

}
```
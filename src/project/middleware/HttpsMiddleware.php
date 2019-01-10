<?php

/*
 * Middleware to ensure there is no trailing /
 * This can prevent human-error.
 */

class HttpsMiddleware
{
    public function __construct()
    {
        // do nothing
    }
    
    
    public function __invoke(Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) : \Slim\Http\Response
    {
        $uri = $request->getUri();
        
        if (strpos($uri, 'http://') !== false) 
        {
            $newLocation = str_replace('http://', 'https://', $uri);
            $response = $response->withHeader('Location', $newLocation);
        }
        else
        {
            $response = $next($request, $response);
        }
        
        return $response;
    }
}

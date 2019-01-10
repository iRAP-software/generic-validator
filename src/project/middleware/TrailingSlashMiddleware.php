<?php

/*
 * Middleware to check using HTTPS and redirect if not
 */

class TrailingSlashMiddleware
{
    public function __construct()
    {
        // do nothing
    }
    
    
    public function __invoke(Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) : \Slim\Http\Response
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        if ($path != '/' && substr($path, -1) == '/') 
        {
            // redirect paths with a trailing slash
            // to their non-trailing counterpart
            $uri = $uri->withPath(substr($path, 0, -1));
            
            if ($request->getMethod() == 'GET') 
            {
                $response = $response->withRedirect((string)$uri, 302);
            }
            else 
            {
                $response = $next($request->withUri($uri), $response);
            }
        }
        else 
        {
            $response = $next($request, $response);
        }
        
        return $response;
    }
}

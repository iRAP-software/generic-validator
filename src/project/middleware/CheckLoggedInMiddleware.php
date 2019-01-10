<?php

/*
 * Middleware to ensure the user is logged in where required.
 */

class CheckLoggedInMiddleware
{
    private $m_publicRoutes;
    private $m_loginURI;
    private $m_isLoggedIn;
    
    
    public function __construct(array $publicRoutes, string $loginURI, bool $isLoggedIn)
    {
        $this->m_isLoggedIn = $isLoggedIn;
        $this->m_publicRoutes = $publicRoutes;
        $this->m_loginURI = $loginURI;
    }
    
    
    public function __invoke(Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) : \Slim\Http\Response
    {
        $route = $request->getAttribute('route');
        $routeName = $route->getName();
        
        if ($this->m_isLoggedIn === FALSE && !in_array($routeName, $this->m_publicRoutes))
        {
            $response = $response->withRedirect($this->m_loginURI, 302);
        }
        else
        {
            $response = $next($request, $response);
        }
        
        return $response;
    }
}

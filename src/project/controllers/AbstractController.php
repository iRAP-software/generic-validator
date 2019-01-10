<?php

/* 
 * Abstract class for a very basic SLIM controller.
 */

abstract class AbstractController
{
    protected $m_request;  /* @var $m_request Slim\Http\Request */
    protected $m_response; /* @var $m_response Slim\Http\Response */
    
    
    public function __construct(\Slim\Http\Request $request, Slim\Http\Response $response)
    {
        $this->m_request = $request;
        $this->m_response = $response;
    }
}


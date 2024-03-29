<?php

/*
 * Abstract for a class that all the "improved" views should extend. 
 */


abstract class AbstractView 
{
    public function render()
    {
        ob_start();
        $this->renderContent();
        return ob_get_clean();
    }
    
    public function __toString() { return $this->render(); }
    
    abstract protected function renderContent();
}
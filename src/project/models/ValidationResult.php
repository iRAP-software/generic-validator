<?php

/* 
 * A simple value object.
 * Be carefule to keep the $passed boolean as it is legitimate to have $errors but still pass
 * in the case of OR relationships in which lots of things can go wrong, but as long as one thing
 * passes, the valiation passes.
 */

class ValidationResult implements JsonSerializable
{
    private $m_passed;
    private $m_errors;
    private $m_warnings;
    
    
    public function __construct(bool $passed, array $errors, array $warnings)
    {
        $this->m_passed = $passed;
        $this->m_errors = $errors;
        $this->m_warnings = $warnings;
    }
    
    
    public function jsonSerialize() 
    {
        return array(
            'passed' => ($this->m_passed) ? 'TRUE' : 'FALSE',
            'errors' => $this->m_errors,
            'warnings' => $this->m_warnings,
        );
    }
    
    
    public function getPassed() { return $this->m_passed; }
    public function getErrors() { return $this->m_errors; }
    public function getWarnings() { return $this->m_warnings; }
}
<?php

class AttributeRule extends \iRAP\MysqlObjects\AbstractTableRowObject implements JsonSerializable
{
    private $m_regexp;
    private $m_name;
    private $m_description;
    private $m_field_id;
    
    
    public function __construct($row, $fieldTypes)
    {
        $this->initializeFromArray($row, $fieldTypes);
    }
    
    
    protected function getAccessorFunctions() 
    {
        return array(
            'field_id'      => function(){ return $this->m_field_id; },
            'regexp'        => function(){ return mysqli_escape_string(iRAP\Mysqli\ConnectionHandler::getConnection(), $this->m_regexp); },
            'name'          => function(){ return $this->m_name; },
            'description'   => function(){ return $this->m_description; },
        );
    }
    
    
    protected function getSetFunctions() 
    {
        return array(
            'field_id'      => function($x){ $this->m_field_id = $x; },
            'regexp'        => function($x){ $this->m_regexp = $x; },
            'name'          => function($x){ $this->m_name = $x; },
            'description'   => function($x){ $this->m_description = $x; },
        );
    }
    
    
    public function getTableHandler(): \iRAP\MysqlObjects\TableInterface 
    {
        return AttributeRuleTable::getInstance();
    }
    
    
    /**
     * Evaluate this rule against a row.
     * @param array $row
     * @return \stdClass
     * @throws Exception
     */
    public function evaluate(array $row) : ValidationResult
    {
        $warnings = array();
        $errors = array();
        
        $fieldName = $this->getField()->get_name();
        
        if (!isset($row[$fieldName]))
        {
            $errors[] = "Missing column: " . $fieldName;
        }
        else
        {
            $pregResult = preg_match($this->m_regexp, $row[$fieldName]);
            
            if ($pregResult === FALSE)
            {
                $msg = "There is an error with the regular expression for " . 
                       "attribute Rule " . $this->m_id . ' - ' . $this->m_regexp;
                
                throw new Exception($msg);
            }
            elseif ($pregResult === 0)
            {
                $errors[] = 'Field: ' . $fieldName . ' Error: ' . $this->m_description . ' Input Value: ' . $row[$fieldName];
            }
        }
        
        $passed = (count($errors) === 0);
        return new ValidationResult($passed, $errors, $warnings);
    }
    
    
    /**
     * Get the field object that this attribute rule applies to.
     * @return \Field
     */
    public function getField() : Field
    {
        return FieldTable::getInstance()->load($this->m_field_id);
    }
    
    
    public function jsonSerialize() 
    {
        return array(
            'field_id'      => $this->m_field_id,
            'regexp'        => $this->m_regexp,
            'name'          => $this->m_name,
            'description'   => $this->m_description,
        );
    }
}
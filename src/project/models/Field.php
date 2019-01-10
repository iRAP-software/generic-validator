<?php

class Field extends \iRAP\MysqlObjects\AbstractTableRowObject implements JsonSerializable
{
    private $m_name;
    private $m_modified_timestamp;
    
    
    public function __construct($row, $fieldTypes=null)
    {
        $this->initializeFromArray($row, $fieldTypes);
    }
    
            
    protected function getAccessorFunctions() 
    {
        return array(
            'name' => function() { return $this->m_name; },
        );
    }
    
    
    protected function getSetFunctions() 
    {
        return array(
            'name'                => function($x) { $this->m_name               = $x; },
            'modified_timestamp'  => function($x) { $this->m_modified_timestamp = $x; }
        );
    }
    
    
    /**
     * Fetch the table handler for this object.
     * @return RuleGroupTable
     */
    public function getTableHandler(): \iRAP\MysqlObjects\TableInterface 
    {
        return FieldTable::getInstance();
    }
    
    
    public function jsonSerialize() 
    {
        return $this->getArrayForm();
    }
    
    
    # Accessors
    public function get_name() { return $this->m_name; }
}
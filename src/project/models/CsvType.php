<?php

/*
 * An object to represent a csv file (just gives us an ID and name).
 * Fields will be assigned to it for when requestors don't provide the field list.
 */

class CsvType extends \iRAP\MysqlObjects\AbstractTableRowObject
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
        return CsvTypeTable::getInstance();
    }
    
    
    /**
     * Create an empty CSV Type object.
     * This is useful for passing to the editor in order to create a new CSV Type object.
     * @return \RuleGroup
     */
    public static function createEmptyCsvTypeObject()
    {
        $dataArray = array(
            'name' => '',
        );
        
        return new CsvType($dataArray);
    }
    
    
    # Accessors
    public function get_name() { return $this->m_name; }
    
    
    # Setters
    public function set_name($x) { return $this->m_name = $x; }
}
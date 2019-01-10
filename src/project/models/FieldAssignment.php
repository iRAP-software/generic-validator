<?php

/**
 * Assigns a field to a CSV file and a column index.
 * A field can be assigned to multiple csv files however the column indexes on the csv file
 * should be unique. E.g. two fields cant be assigned to a csv file and have the same column index.
 */

class FieldAssignment extends \iRAP\MysqlObjects\AbstractTableRowObject
{
    private $m_field_id;
    private $m_csv_type_id;
    private $m_column_id;
    private $m_modified_timestamp;
    
    
    public function __construct($row, $fieldTypes=null)
    {
        $this->initializeFromArray($row, $fieldTypes);
    }
    
            
    protected function getAccessorFunctions() 
    {
        return array(
            'field_id' => function() { return $this->m_field_id; },
            'csv_type_id' => function() { return $this->m_csv_type_id; },
            'column_id' => function() { return $this->m_column_id; },
        );
    }
    
    
    protected function getSetFunctions() 
    {
        return array(
            'field_id'            => function($x) { $this->m_field_id = $x; },
            'csv_type_id'         => function($x) { $this->m_csv_type_id = $x; },
            'column_id'           => function($x) { $this->m_column_id = $x; },
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
    
    
    # Accessors
    public function get_field_id() { return $this->m_field_id; }
    public function get_csv_type_id() { return $this->m_csv_type_id; }
    public function get_column_id() { return $this->m_column_id; }
    public function get_modified_timestamp() { return $this->m_modified_timestamp; }
}
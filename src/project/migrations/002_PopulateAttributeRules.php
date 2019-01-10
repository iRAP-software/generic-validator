<?php

/*
 * Create the table for grouping rule groups and attribute rules.
 */

class PopulateAttributeRules302 implements \iRAP\Migrations\MigrationInterface
{
    public function __construct(){}
    
    
    public function up(\mysqli $mysqliConn) 
    {
        $non_empty_string_regexp = '^(?!\s*$).+';
        $date_regexp = '^([0]?[1-9]|[1|2][0-9]|[3][0|1])[/]([0]?[1-9]|[1][0-2])[/]([0-9]{4}|[0-9]{2})$';
        $regexp_two_decimal_places = '^[0-9]+(\.[0-9]{1,2})?$';
        $regexp_decimal = '/\d+\.?\d*/'; // a number and any number of decimal places.
        $regexp_positive_integer = '^\d+$';
        
        $regexp_range = function($start, $end)
        {
            return '/^[' . $start . '-' . $end . ']$/';
        };
        
        $rows = array();
        
        $rows[] = array(
            'column_number' => 1,
            'regexp' => $non_empty_string_regexp,
            'name' => 'Example attribute rule 1',
            'description' => 'Example attribute rule to be validated against a non-empty string.',
        );
        
        $rows[] = array(
            'column_number' => 2,
            'regexp' => $date_regexp,
            'name' => 'Example attribute rule 2',
            'description' => 'Example attribute rule to be validated against a date in dd/mm/yyyy format.',
        );
                
        $rows[] = array(
            'column_number' => 3,
            'regexp' => $regexp_two_decimal_places,
            'name' => 'Example attribute rule 3',
            'description' => 'Example attribute rule to be validated against a number with up to 2 decimal places',
        );
                
        $rows[] = array(
            'column_number' => 4,
            'regexp' => $regexp_range(1,5),
            'name' => 'Example attribute rule 4',
            'description' => 'Example attribute rule to be validated against an int between 1 and 5',
        );
                
        $rows[] = array(
            'column_number' => 5,
            'regexp' => $regexp_positive_integer,
            'name' => 'Example attribute rule 5',
            'description' => 'Example attribute rule to be validated against a positive integer',
        );
                
        $rows[] = array(
            'column_number' => 6,
            'regexp' => $regexp_decimal,
            'name' => 'Example attribute rule 6',
            'description' => 'Example attribute rule to be validated against any decimal',
        );
        
        $query = iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery($rows, 'attribute_rules', $mysqliConn);
        $result = $mysqliConn->query($query);
        
        if ($result === false)
        {
            throw new Exception('Failed to populate attribute rules table');
        }
        
        # Assign them all to a rule group
        $row = array(
            'id'           => 1,
            'relationship' => 1, # AND or OR (1, 2)
            'name'         => 'Example rule group name',
            'description'  => 'Simple regexps on each individual column to check they are the right type and in the right range'
        );
        
        
        $query = 
            "INSERT INTO `rule_groups` " .
            "SET " . iRAP\CoreLibs\MysqliLib::generateQueryPairs($row, $mysqliConn);
                
        $result = $mysqliConn->query($query);
        
        if ($result === false)
        {
            throw new Exception('Failed to insert our first rule group');
        }
        
        
        $query = 
            "INSERT INTO `attribute_rule_assignments` (attribute_rule_id, rule_group_id) " .
            "SELECT `id`, '1' FROM `attribute_rules`";
        
        $result = $mysqliConn->query($query);
        
        if ($result === false)
        {
            throw new Exception('Failed to insert our first rule group');
        }
    }    
    
    
    public function down(\mysqli $mysqliConn) 
    {
        $query = 'TRUNCATE TABLE attribute_rules';
        $mysqliConn->query($query);
    }
}

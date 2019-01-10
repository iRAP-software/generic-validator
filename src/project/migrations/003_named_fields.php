<?php

/*
 * Create the table for grouping rule groups and attribute rules.
 */

class NamedFields implements \iRAP\Migrations\MigrationInterface
{
    public function __construct(){}
    
    
    public function up(\mysqli $mysqliConn) 
    {
        $this->createTables($mysqliConn);
        $this->addFields($mysqliConn);
        $this->addCsvTypes($mysqliConn);
        $this->addFieldAssignments($mysqliConn);
        $this->addRuleGroups($mysqliConn);
        $this->addAttributeRules($mysqliConn);
        $this->addAttributeRuleAssignments($mysqliConn);
    }
    
    
    private function createTables(mysqli $mysqliConn)
    {
        $queries = array();
        
        // grouping
        $queries[] = 
            'CREATE TABLE `csv_type` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `modified_timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        
        
        // object to represent the name of a column in the csv file.
        // multiple attribute rules can be assigned to a field name.
        // but not using FK so that same attribute rule can be assigned to multiple csv files.
        // E.g. coder_name
        $queries[] = 
            'CREATE TABLE `field` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `modified_timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY(`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        
        
        // Assign fields to a csv_type.
        // Only one field can be assiged to one column in a csv file, but a field may be 
        // assigned to multiple csv files and with the same or different column_ids.
        $queries[] = 
            'CREATE TABLE `field_assignment` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `field_id` INT UNSIGNED NOT NULL,
                `csv_type_id` INT UNSIGNED NOT NULL,
                `column_id` INT UNSIGNED NOT NULL,
                `modified_timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY(`csv_type_id`, `column_id`),
                UNIQUE KEY(`csv_type_id`, `field_id`),
                FOREIGN KEY(`field_id`) REFERENCES `field` (`id`),
                FOREIGN KEY(`csv_type_id`) REFERENCES `csv_type` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
        
        $queries[] = 'DELETE FROM `attribute_rule_assignments`';
        $queries[] = 'DELETE FROM `attribute_rules`';
        
        $queries[] = 'ALTER TABLE `attribute_rules` DROP COLUMN `column_number`';
        $queries[] = 'ALTER TABLE `attribute_rules` ADD COLUMN `field_id` INT UNSIGNED NOT NULL';
        
        $queries[] = 
            'ALTER TABLE `attribute_rules` 
            ADD CONSTRAINT `f_key_column1`
            FOREIGN KEY(`field_id`) REFERENCES `field` (`id`)
            ON DELETE RESTRICT ON UPDATE CASCADE';
        
        foreach ($queries as $query)
        {
            $result = $mysqliConn->query($query);
            
            if ($result === FALSE)
            {
                print "Migration query failed:" . PHP_EOL;
                print $query . PHP_EOL;
                print $mysqliConn->error . PHP_EOL;
                die();
            }
        }
    }
    
    private function addFields(mysqli $mysqli)
    {
        $fields = array(
            array('id' => 2, 'name' => 'Field1'),
            array('id' => 3, 'name' => 'Field2'),
            array('id' => 4, 'name' => 'Field3'),
        );
        
        $query = \iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery($fields, 'field', $mysqli);
        $result = $mysqli->query($query);
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to insert fields.");
        }
    }
    
    
    private function addCsvTypes(mysqli $mysqli)
    {
        $data = array(
            array(
                'id' => 1,
                'name' => 'CSV_type1',
            ),
            array(
                'id' => 2,
                'name' => 'CSV_type2',
            ),
        );
        
        $query = \iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery($data, 'csv_type', $mysqli);
        $result = $mysqli->query($query);
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to insert csv tyes.");
        }
    }
    
    
    private function addFieldAssignments(mysqli $mysqli)
    {
        $fields1 = array(
            array('csv_type_id' => 1, 'field_id' => 2, 'column_id' => 1),
            array('csv_type_id' => 1, 'field_id' => 3, 'column_id' => 2),
            array('csv_type_id' => 1, 'field_id' => 4, 'column_id' => 3),
        );
        
        $query1 = \iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery(
            $fields1, 
            'field_assignment', 
            $mysqli
        );
        
        $result1 = $mysqli->query($query1);
        
        if ($result1 === FALSE)
        {
            throw new Exception("Failed to insert CSV_type1 field assignments.");
        }

        $fields2 = array(
            array('csv_type_id' => 2, 'field_id' => 2, 'column_id' => 1),
            array('csv_type_id' => 2, 'field_id' => 3, 'column_id' => 2),
            array('csv_type_id' => 2, 'field_id' => 4, 'column_id' => 3),
        );
        
        $query2 = \iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery(
            $fields2, 
            'field_assignment', 
            $mysqli
        );
        $result2 = $mysqli->query($query2);
        
        if ($result2 === FALSE)
        {
            throw new Exception("Failed to insert CSV_type2 field assignments.");
        }
    }
    
    
    private function addRuleGroups(mysqli $mysqliConn)
    {
        $deleteRuleGroupsQuery = "DELETE FROM `rule_groups`";
        $deleteResult = $mysqliConn->query($deleteRuleGroupsQuery);
        
        if ($deleteResult === FALSE)
        {
            throw new Exception("Failed to delete existing rule groups");
        }
        
        $ruleGroups = array(
            array('id' => 1, 'name' => 'Rule group 1', 'description' => 'Description for Rule group 1', 'relationship' => RELATIONSHIP_AND),
            array('id' => 2, 'name' => 'Rule group 2', 'description' => 'Description for Rule group 2', 'relationship' => RELATIONSHIP_AND),
        );
        
        $addRuleGroupsQuery = \iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery($ruleGroups, 'rule_groups', $mysqliConn);
        $addRuleGroupsResult = $mysqliConn->query($addRuleGroupsQuery);
        
        if ($addRuleGroupsResult === FALSE)
        {
            print "Failed to add the base rule groups" . PHP_EOL;
            print $addRuleGroupsQuery . PHP_EOL;
            print $mysqliConn->error . PHP_EOL;
            die();
        }
    }
    
    private function addAttributeRules(mysqli $mysqliConn)
    {
        $attributeRules1 = array(
            array('id' => 1,  'name' => 'Attribute rule 1',  'field_id' =>  2, 'regexp' => '/^.+$/', 'description' => 'Attribute cannot be empty.'),
            array('id' => 2,  'name' => 'Attribute rule 2',  'field_id' =>  3, 'regexp' => '/^[1-5]$/', 'description' => 'Attribute must be an integer between 1-5.'),
            array('id' => 3,  'name' => 'Attribute rule 3',  'field_id' =>  4, 'regexp' => '/^[0-9]+([.][0-9]+)?$/', 'description' => 'Attribute must be a number'),
        );
        
        $attributeRulesQuery1= \iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery(
            $attributeRules1, 
            'attribute_rules', 
            $mysqliConn
        );
        
        $result1 = $mysqliConn->query($attributeRulesQuery1);
        
        if ($result1 === FALSE)
        {
            throw new Exception("Failed to insert attribute rules (1).");
        }
        
        $attributeRules2 = array(
            array('id' => 4, 'name' => 'Attribute rule 4', 'field_id' =>  2, 'regexp' => '/^.+$/', 'description' => 'Attribute cannot be empty.'),
            array('id' => 5, 'name' => 'Attribute rule 5', 'field_id' =>  3, 'regexp' => '/^[1-5]$/', 'description' => 'Attribute must be an integer between 1-5.'),
            array('id' => 6, 'name' => 'Attribute rule 6', 'field_id' =>  4, 'regexp' => '/^[0-9]+([.][0-9]+)?$/', 'description' => 'Attribute must be a number'),
        );
        
        $attributeRulesQuery2 = \iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery(
            $attributeRules2, 
            'attribute_rules', 
            $mysqliConn
        );
        
        $result2 = $mysqliConn->query($attributeRulesQuery2);
        
        if ($result2 === FALSE)
        {
            throw new Exception("Failed to insert attribute rules (2).");
        }
    }
    
    
    private function addAttributeRuleAssignments(mysqli $mysqliConn)
    {
        $attributeRuleAssignments1 = array(
            array('id' =>  1, 'attribute_rule_id' =>  1, 'rule_group_id' => 1 ),
            array('id' =>  2, 'attribute_rule_id' =>  2, 'rule_group_id' => 1 ),
            array('id' =>  3, 'attribute_rule_id' =>  3, 'rule_group_id' => 1 ),
        );
        
        $attributeRuleAssignments1 = array(
            array('id' => 4, 'attribute_rule_id' => 4, 'rule_group_id' => 2 ),
            array('id' => 5, 'attribute_rule_id' => 5, 'rule_group_id' => 2 ),
            array('id' => 6, 'attribute_rule_id' => 6, 'rule_group_id' => 2 ),
        );
        
        $allAssignments = array_merge($attributeRuleAssignments1, $attributeRuleAssignments2);
        
        $assignmentsQuery = \iRAP\CoreLibs\MysqliLib::generateBatchInsertQuery(
            $allAssignments, 
            'attribute_rule_assignments', 
            $mysqliConn
        );
        
        $result = $mysqliConn->query($assignmentsQuery);
        
        if ($result === FALSE)
        {
            print "Failed to insert attribute rule assignments." . PHP_EOL;
            print $assignmentsQuery . PHP_EOL;
            print $mysqliConn->error . PHP_EOL;
            die();
        }
    }
    
    
    
    public function down(\mysqli $mysqliConn) 
    {
        
    }
}

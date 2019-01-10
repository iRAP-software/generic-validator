<?php

/*
 * 
 */

class CreateAttributeRulesTable implements \iRAP\Migrations\MigrationInterface
{
    public function __construct(){}
    
    public function up(\mysqli $mysqliConn) 
    {
        $this->createRelationshipsTable($mysqliConn);
        $this->createAttributeRulesTable($mysqliConn);
        $this->createRuleGroupsTable($mysqliConn);
        $this->createRuleGroupAssignmentsTable($mysqliConn);
        $this->createAttributeRuleAssignmentsTable($mysqliConn);
    }
    
    
    /**
     * Create a table for assigning the attribute_rules to groups. Attribute rules are rules
     * that specify what regexp applies to a column number.
     * @param mysqli $mysqliConn
     */
    private function createAttributeRuleAssignmentsTable(mysqli $mysqliConn)
    {
        $query = 
            'CREATE TABLE `attribute_rule_assignments` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `attribute_rule_id` int unsigned NOT NULL,
                `rule_group_id` int unsigned NOT NULL,
                `modified_timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY (`attribute_rule_id`, `rule_group_id`),
                FOREIGN KEY (attribute_rule_id) REFERENCES attribute_rules(id) ON DELETE RESTRICT ON UPDATE CASCADE,
                FOREIGN KEY (rule_group_id) REFERENCES rule_groups(id) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        
        $result = $mysqliConn->query($query);
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to create the attribute_rule_assignments table." . $mysqliConn->error);
        }
    }
    
    
    /**
     * Create a table for rules that specify which column number needs to match what regexp.
     * We also have a name for the rule and a description for what it is checking.
     * @param mysqli $mysqliConn
     */
    private function createAttributeRulesTable(mysqli $mysqliConn)
    {
        $query = 
            'CREATE TABLE `attribute_rules` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text NOT NULL,
                `column_number` INT unsigned NOT NULL,
                `regexp` VARCHAR(255) NOT NULL,
                `modified_timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        
        $result = $mysqliConn->query($query);
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to create the rule_groups table." . $mysqliConn->error);
        }
    }
    
    
    /**
     * Create a table for the type of relationships there can be for the rule groups. For now this
     * is just AND, or OR/ANY. 
     * @param mysqli $mysqliConn
     */
    private function createRelationshipsTable(mysqli $mysqliConn)
    {
        $queries = array();
        
        $queries[] = 
            'CREATE TABLE `relationships` (
                `id` TINYINT unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text NOT NULL,
                `modified_timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        
        $queries[] = 
            "INSERT INTO `relationships` (id, name, description) VALUES " . 
            "(1, 'AND', 'A relationship whereby ALL rules must evaludate true.'), " .
            "(2, 'OR', 'A relationship whereby if ANY of rules must evaludate true, this returns TRUE.')";
        
        foreach ($queries as $query)
        {
            $result = $mysqliConn->query($query);
        
            if ($result === FALSE)
            {
                throw new Exception("Failed to create the relationships table." . $mysqliConn->error);
            }
        }
    }
    
    
    /**
     * Create a table for the groups of rules. This just provides the unique id, name, and
     * relationship for group. Any number of rules can be assigned to a group, as long as they
     * all have the same relationship. E.g. a relationship where all rules must be met or any
     * of the rules must be met.
     * @param mysqli $mysqliConn
     */
    private function createRuleGroupsTable(mysqli $mysqliConn)
    {
        $query = 
            'CREATE TABLE `rule_groups` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text NOT NULL,
                `relationship` TINYINT unsigned NOT NULL,
                `modified_timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                FOREIGN KEY (relationship) REFERENCES relationships(id) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        
        $result = $mysqliConn->query($query);
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to create the rule_groups table." . $mysqliConn->error);
        }
    }
    
    
    /**
     * Create a table for holding the assignments of groups to another group.
     * @param mysqli $mysqliConn
     */
    private function createRuleGroupAssignmentsTable(mysqli $mysqliConn)
    {
        $query = 
            'CREATE TABLE `rule_group_assignments` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `child_group_id` int unsigned NOT NULL,
                `parent_group_id` int unsigned NOT NULL,
                `modified_timestamp` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY (`child_group_id`, `parent_group_id`),
                FOREIGN KEY (child_group_id) REFERENCES rule_groups(id) ON DELETE RESTRICT ON UPDATE CASCADE,
                FOREIGN KEY (parent_group_id) REFERENCES rule_groups(id) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        
        $result = $mysqliConn->query($query);
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to create the rule_group_assignments table. " . $mysqliConn->error);
        }
    }
    
    
    public function down(\mysqli $mysqliConn) 
    {
        $tables = array(
            'relationships',
            'attribute_rules',
            'rule_groups',
            'attribute_rule_assignments',
            'rule_group_assignments'
        );
        
        foreach ($tables as $table)
        {
            $query = "DROP TABLE `" . $table . "`";
            $mysqliConn->query($query);
        }
    }
}

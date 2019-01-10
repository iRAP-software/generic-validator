<?php

class AttributeRuleAssignmentTable extends iRAP\MysqlObjects\AbstractTable
{
    public function getDb(): \mysqli
    {
        return SiteSpecific::getDb();
    }

    public function getFieldsThatAllowNull(): array 
    {
        return array();
    }

    public function getFieldsThatHaveDefaults(): array
    {
        return array(
            'modified_timestamp'
        );
    } 

    public function getObjectClassName() 
    {
        return 'AttributeRuleAssignment';
    }

    public function getTableName() 
    {
        return 'attribute_rule_assignments';
    }

    public function validateInputs(array $data): array 
    {
        return $data;
    }
}
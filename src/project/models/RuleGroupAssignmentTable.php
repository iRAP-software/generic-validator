<?php

class RuleGroupAssignmentTable extends AbstractValidatorTable
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
        return 'modified_timestamp';
    } 

    public function getObjectClassName() 
    {
        return 'RuleGroupAssignment';
    }

    public function getTableName() 
    {
        return 'rule_group_assignements';
    }

    public function validateInputs(array $data): array 
    {
        return $data;
    }
}
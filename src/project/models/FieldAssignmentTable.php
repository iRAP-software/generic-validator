<?php

class FieldAssignmentTable extends AbstractValidatorTable
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
        return 'FieldAssignment';
    }

    public function getTableName() 
    {
        return 'field_assignment';
    }

    public function validateInputs(array $data): array 
    {
        return $data;
    }
}
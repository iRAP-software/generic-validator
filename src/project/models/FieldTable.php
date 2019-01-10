<?php

class FieldTable extends AbstractValidatorTable
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
        return array('modified_timestamp');
    } 

    public function getObjectClassName() 
    {
        return 'Field';
    }

    public function getTableName() 
    {
        return 'field';
    }

    public function validateInputs(array $data): array 
    {
        return $data;
    }
}
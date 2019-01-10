<?php

class CsvTypeTable extends AbstractValidatorTable
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
        return 'CsvType';
    }

    public function getTableName() 
    {
        return 'csv_type';
    }

    public function validateInputs(array $data): array 
    {
        return $data;
    }
}
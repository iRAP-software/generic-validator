<?php

class RuleGroupTable extends AbstractValidatorTable
{
    public function getFieldsThatAllowNull(): array 
    {
        return array();
    }
    
    
    public function getFieldsThatHaveDefaults() 
    {
        return array(
            'modified_timestamp'
        );
    } 
    
    
    public function getObjectClassName() { return 'RuleGroup'; }

    public function getTableName() { return 'rule_groups'; }
    
    
    public function validateInputs(array $data): array 
    {
        return $data;
    }
    
    
    /**
     * Given the "type" of file to validate specified in the request, get the ID of the "master"
     * rule group to evaluate to determine if the file passes validation or not.
     * @param string $type
     * @return RuleGroup
     * @throws Exception - if the type is not recognised.
     */
    public static function getRuleGroupForTypeString(string $type) : RuleGroup
    {
        $lowercaseType = strtolower($type);
        
        $map = array(
            'Rule Group1'    => 1,
            'Rule Group 2'   => 2,
        );
        
        if (!isset($map[$lowercaseType]))
        {
            throw new Exception("Unrecognized type specified: {$type}", 400);
        }
        
        return RuleGroupTable::getInstance()->load($map[$lowercaseType]);
    }
}
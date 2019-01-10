<?php

class AttributeRuleAssignment extends \iRAP\MysqlObjects\AbstractTableRowObject
{
    private $m_attributeRuleId;
    private $m_ruleGroupId;
    
    protected function getAccessorFunctions() 
    {
        return array(
            'attribute_rule_id' => function(){ return $this->m_attributeRuleId; },
            'rule_group_id'     => function(){ return $this->m_ruleGroupId; },
        );
    }

    protected function getSetFunctions() 
    {
        return array(
            'attribute_rule_id' => function($x){ $this->m_attributeRuleId = intval($x); },
            'rule_group_id'     => function($x){ $this->m_ruleGroupId = intval($x); },
        );
    }
    

    public function getTableHandler(): \iRAP\MysqlObjects\TableInterface 
    {
        return AttributeRuleAssignmentTable::getInstance();
    }
}
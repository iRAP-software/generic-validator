<?php

class RuleGroupAssignment extends \iRAP\MysqlObjects\AbstractTableRowObject
{
    private $m_childGroupId;
    private $m_parentGroupId;
    private $m_modified_timstamp;

    protected function getAccessorFunctions() {
        return array(
            'child_group_id'  => function(){ return $this->m_childGroupId; },
            'parent_group_id' => function(){ return $this->m_parentGroupId; }
        );
    }

    protected function getSetFunctions() {
        return array(
            'child_group_id'     => function($x){ $this->m_childGroupId = $x; },
            'parent_group_id'    => function($x){ $this->m_parentGroupId = $x; },
            'modified_timestamp' => function($x){ $this->m_modified_timestamp = $x; },
        );
    }
    
    
    public function getTableHandler(): \iRAP\MysqlObjects\TableInterface 
    {
        return RuleGroupAssignmentTable::getInstance();
    }
}
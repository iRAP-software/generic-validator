<?php

class AttributeRuleTable extends AbstractValidatorTable
{
    public function getFieldsThatAllowNull(): array
    {
        return array();
    }

    public function getFieldsThatHaveDefaults() : array
    {
        return array(
            'modified_timstamp'
        );
    } 

    public function getObjectClassName() 
    {
        return 'AttributeRule';
    }

    public function getTableName() 
    {
        return 'attribute_rules';
    }

    public function validateInputs(array $data): array 
    {
        return $data;
    }
    
    
    
    /**
     * Load the attribute rules that are direct children of the specified rule group.
     * @param RuleGroup $ruleGroup - the rule group to get the attribute rules for.
     * @return array - array list of AttributeRule objects.
     * @throws Exception
     */
    public function loadForRuleGroup(RuleGroup $ruleGroup) : array
    {
        $subQuery = "SELECT `attribute_rule_id` FROM `attribute_rule_assignments` " . 
                    "WHERE `rule_group_id`='" . $ruleGroup->get_id() . "'";
        
        $query = "SELECT * FROM `" . $this->getTableName() . "` WHERE `id` IN(" . $subQuery . ")";
        
        $result = $this->getDb()->query($query);
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to select attribute rules that belong to a rule group");
        }
        
        return $this->convertMysqliResultToObjects($result);
    }
    
    
    /**
     * Removes all assignments of attribute rules to this group and then cleans up any orphans.
     * We do not just delete attribute rules that are assigned to this rule group, because they
     * may also be being used by other rule groups.
     * @param RuleGroup $ruleGroup
     * @throws Exception
     */
    public function deleteForRuleGroup(RuleGroup $ruleGroup)
    {
        $where = array('rule_group_id' => $ruleGroup->get_id());
        AttributeRuleAssignmentTable::getInstance()->deleteWhereAnd($where);
        
        // Remove attribute rules that are not assigned to anybody (some may have been assigned to
        // this rule group and another one and thus shouldnt be deleted).
        
        $subQuery = "SELECT `attribute_rule_id` FROM `attribute_rule_assignments`";
        $query = "DELETE FROM `attribute_rules` WHERE id NOT IN(" . $subQuery . ")";
        
        $mysqli = $this->getDb();
        $result = $mysqli->query($query);
        
        if ($result === FALSE)
        {
            throw new Exception("Failed to delete orphan attribute rules.");
        }
    }
}
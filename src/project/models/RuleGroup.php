<?php

class RuleGroup extends \iRAP\MysqlObjects\AbstractTableRowObject implements JsonSerializable
{
    private $m_relationship;
    private $m_name;
    private $m_description;
    
    
    # Non database attributes
    private $m_errors = array();
    private $m_warnings = array();
    
    
    
    public function __construct($row, $fieldTypes=null)
    {
        $this->initializeFromArray($row, $fieldTypes);
    }
    
    
    /**
     * Create an empty rule group object.
     * This is useful for passing to the editor in order to create a new rule group object.
     * @return \RuleGroup
     */
    public static function createEmptyRuleGroupObject()
    {
        $dataArray = array(
            'name' => '',
            'relationship' => RELATIONSHIP_AND,
            'description' => '',
        );
        
        return new RuleGroup($dataArray);
    }
    
            
    protected function getAccessorFunctions() 
    {
        return array(
            'relationship' => function() { return $this->m_relationship; },
            'name'         => function() { return $this->m_name; },
            'description'  => function() { return $this->m_description; }
        );
    }
    
    
    protected function getSetFunctions() 
    {
        return array(
            'relationship' => function($x) { $this->m_relationship = intval($x); },
            'name'         => function($x) { $this->m_name         = $x; },
            'description'  => function($x) { $this->m_description  = $x; }
        );
    }
    
    
    /**
     * Fetch all the Rule Groups that have this rule group as their direct parent.
     * @return array<RuleGroup>
     */
    public function fetchSubRuleGroups($parentRuleGroupId)
    {
        $subGroups = array();
        
        $subRuleGroupsQuery = 
            "SELECT * FROM `rule_groups` WHERE `id` IN (" .
                "SELECT `child_group_id` FROM `rule_group_assignments` " . 
                "WHERE `parent_group_id`='" . $parentRuleGroupId . "' " .
            ")";
        
        $errMsg = "Failed to select sub rule groups";
        /* @var $result \mysqli_result */
        $result = RuleGroupTable::getInstance()->getDb()->query($subRuleGroupsQuery);
        
        if ($result === FALSE)
        {
            throw new Exception($errMsg);
        }
        
        while (($row = $result->fetch_assoc()) != null)
        {
            $subGroups[] = self::create_from_db_row($row);
        }
        
        return $subGroups;
    }
    
    
    /**
     * Evaluate whether this rule group "passed" or "failed";
     * @param array $row
     */
    public function evaluate(array $row) : ValidationResult
    {
        $passed = false;
        $errors = array();
        $warnings = array();
        
        $subRuleGroups = $this->fetchSubRuleGroups($this->m_id);
        $attributeRuleTable = AttributeRuleTable::getInstance();
        $attributeRules = $attributeRuleTable->loadForRuleGroup($this);
        
        switch ($this->m_relationship)
        {
            case RELATIONSHIP_AND:
            {
                # AND relationsip (if anything fails the whole thing fails)
                $passed = true;
                
                foreach ($attributeRules as $attributeRule)
                {
                    /* @var $attributeRule AttributeRule */
                    $validationResult = $attributeRule->evaluate($row);
                    $errors = array_merge($errors, $validationResult->getErrors());
                    $warnings = array_merge($warnings, $validationResult->getWarnings());
                    
                    /*@var $attributeRule AttributeRule */
                    if ($validationResult->getPassed() == FALSE)
                    {
                        $passed = false;
                    }
                    
                    if (count($errors) > MAX_ERRORS)
                    {
                        break;
                    }
                }

                if ($passed)
                {
                    foreach ($subRuleGroups as $subRuleGroup)
                    {
                        /* @var $subRuleGroup RuleGroup */
                        $validationResult = $subRuleGroup->evaluate($row);
                        $errors = array_merge($errors, $validationResult->getErrors());
                        $warnings = array_merge($warnings, $validationResult->getWarnings());

                        if ($validationResult->getPassed() == false)
                        {
                            $passed = false;
                        }
                        
                        if (count($errors) > MAX_ERRORS)
                        {
                            break;
                        }
                    }
                }
            }
            break;
            
            case RELATIONSHIP_OR:
            {
                # OR relationsip (if anything passes the whole thing passes)
                $passed = false;

                foreach ($attributeRules as $attributeRule)
                {
                    /*@var $attributeRule AttributeRule */
                    $validationResult = $attributeRule->evaluate($row);
                    $errors = array_merge($errors, $validationResult->getErrors());
                    $warnings = array_merge($warnings, $validationResult->getWarnings());
                    
                    if ($validationResult->getPassed())
                    {
                        $passed = true;
                        break;
                    }
                }
                
                if ($passed === FALSE)
                {
                    foreach ($subRuleGroups as $subRuleGroup)
                    {
                        /* @var $subRuleGroup RuleGroup */
                        
                        $validationResult = $subRuleGroup->evaluate($row);
                        
                        $errors = array_merge($errors, $validationResult->getErrors());
                        $warnings = array_merge($warnings, $validationResult->getWarnings());
                        
                        if ($validationResult->getPassed() == true)
                        {
                            $passed = true;
                            break;
                        }
                    }
                }
            }
            break;
            
            default:
            {
                throw new Exception("Unrecognized rule group relationship.");
            }
            break;
        }
        
        return new ValidationResult($passed, $errors, $warnings);
    }
    
    
    /**
     * Fetch the table handler for this object.
     * @return RuleGroupTable
     */
    public function getTableHandler(): \iRAP\MysqlObjects\TableInterface 
    {
        return RuleGroupTable::getInstance();
    }
    
    
    public function jsonSerialize() 
    {
        return array(
            'relationship' => (string)$this->m_relationship,
            'name' => $this->m_name,
            'description' => $this->m_description,
            'errors' => $this->m_errors,
            'warnings' => $this->m_warnings,
        );
    }
    
    
    # Accessors
    public function get_relationship() { return $this->m_relationship; }
    public function get_name()         { return $this->m_name; }
    public function get_description()  { return $this->m_description; }
}
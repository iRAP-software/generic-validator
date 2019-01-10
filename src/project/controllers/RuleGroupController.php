<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RuleGroupController extends AbstractController
{
    /**
     * Display the overview page for viewing your rule groups to edit or add one.
     */
    public function overview()
    {
        $ruleGroups = RuleGroupTable::getInstance()->loadAll();
        $ruleGroupOverview = new RuleGroupOverviewView(...$ruleGroups);
        $templateView = new TemplateView($ruleGroupOverview);
        $response = $this->m_response->write($templateView);
        return $response;
    }
    
    
    /**
     * Display the web view for creating a rule group.
     */
    public function create()
    {
        $emptyRuleGroup = RuleGroup::createEmptyRuleGroupObject();
        $fields = FieldTable::getInstance()->loadAll();
        $ruleGroupEditorView = new RuleGroupEditorView($emptyRuleGroup, $fields);
        $templateView = new TemplateView($ruleGroupEditorView);
        $response = $this->m_response->write($templateView);
        return $response;
    }
    
    /**
     * Display the web view for editing a rule group.
     */
    public function edit(int $id)
    {
        $ruleGroup = RuleGroupTable::getInstance()->load($id);
        
        /* @var $attributeRulesTable AttributeRuleTable */
        $attributeRulesTable = AttributeRuleTable::getInstance();
        $attributeRules = $attributeRulesTable->loadForRuleGroup($ruleGroup);
        $fields = FieldTable::getInstance()->loadAll();
        $ruleGroupEditorView = new RuleGroupEditorView($ruleGroup, $fields, ...$attributeRules);
        $templateView = new TemplateView($ruleGroupEditorView);
        $response = $this->m_response->write($templateView);
        return $response;
    }
    
    
    /**
     * Handle the submission of the create/edit rule group form
     */
    public function handleEdit(int $ruleGroupID)
    {
        $existingRuleGroup = RuleGroupTable::getInstance()->load($ruleGroupID);
        
        $allPostPutVars = $this->m_request->getParsedBody();
                
        if (!isset($allPostPutVars['name']))
        {
            throw new Exception("Missing required name");
        }
        
        if (!isset($allPostPutVars['attributes']))
        {
            throw new Exception("Missing required 'attributes' field");
        }
        
        $name = $allPostPutVars['name'];
        $desiredAttributes = $allPostPutVars['attributes'];
        
        $existingRuleGroup->update(array('name' => $name));
                
        // Clear out any existing attributes for the rule group before inserting fresh ones.
        /* @var $attributeRuleTable AttributeRuleTable */
        $attributeRuleTable = AttributeRuleTable::getInstance();
        $attributeRuleTable->deleteForRuleGroup($existingRuleGroup);
        
        // Now insert the attributes
        $columnCounter = 0;
        foreach ($desiredAttributes as $desiredAttribute)
        {
            $row = array(
                'field_id' => $desiredAttribute['field_id'],
                'name' => $desiredAttribute['name'],
                'regexp' => $desiredAttribute['regexp'],
                'description' => $desiredAttribute['description'],
            );
            
            $attributeRule = AttributeRuleTable::getInstance()->create($row);
            
            // now create the assignment to assign it to the rule group.
            AttributeRuleAssignmentTable::getInstance()->create(array(
                'attribute_rule_id' => $attributeRule->get_id(),
                'rule_group_id' => $existingRuleGroup->get_id(),
            ));
            
            $columnCounter++;
        }
        
        $responseData = array('result' => 'success');
        return SiteSpecific::getJsonResponse($responseData, $this->m_response);
    }
    
    
    /**
     * Handle the submission of the create/edit rule group form
     */
    public function handleCreate()
    {
        die('handle create');
        return $response;
    }
}
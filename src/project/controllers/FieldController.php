<?php

/* 
 * 
 */

class FieldController extends AbstractController
{
    /**
     * Display the overview page for viewing your rule groups to edit or add one.
     */
    public function overview()
    {
        $fields = FieldTable::getInstance()->loadAll();
        $fieldsOverview = new FieldOverviewView(...$fields);
        $templateView = new TemplateView($fieldsOverview);
        $response = $this->m_response->write($templateView);
        return $response;
    }
    
    
    /**
     * Display the web view for creating a rule group.
     */
    public function create()
    {
        $allPostPutVars = $this->m_request->getParsedBody();
        
        if (!isset($allPostPutVars['field_name']))
        {
            die("Missing required field name");
        }
        
        $fieldName = $allPostPutVars['field_name'];
        
        $result = preg_match('/^[1-9a-zA-Z_]+$/', $fieldName);
        
        if ($result === FALSE)
        {
            throw new Exception("Something went wrong comparing field name agaist regexp.");
        }
        
        if ($result === 0)
        {
            die("Field names can only be alphanumeric with underscores.");
        }
        
        $fieldArray = array('name' => $allPostPutVars['field_name']);
        FieldTable::getInstance()->create($fieldArray);
        
        // Redirect back to the fields overview page where they will now see the new field
        $response = $this->m_response->withRedirect('/admin/field', 302);
        return $response;
    }
    
    
    /**
     * Handle the deletion of a field by ID 
     */
    public function delete(int $id)
    {
        FieldTable::getInstance()->delete($id);
        $response = $this->m_response->withRedirect('/admin/field', 302);
        return $response;
    }
}
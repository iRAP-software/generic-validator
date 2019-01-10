<?php

/* 
 * 
 */

class CsvTypeController extends AbstractController
{
    /**
     * Display the overview page for viewing your rule groups to edit or add one.
     */
    public function overview()
    {
        $csvTypes = CsvTypeTable::getInstance()->loadAll();
        $csvTypeOverview = new CsvFilesOverviewView(...$csvTypes);
        $templateView = new TemplateView($csvTypeOverview);
        $response = $this->m_response->write($templateView);
        return $response;
    }
    
    
    /**
     * Display the web view for creating a rule group.
     */
    public function create()
    {
        $emptyCsvType = CsvType::createEmptyCsvTypeObject();
        $editorView = new CsvEditorView($emptyCsvType);
        $templateView = new TemplateView($editorView);
        $response = $this->m_response->write($templateView);
        return $response;
    }
    
    
    /**
     * Display the web view for editing an existing a rule group.
     */
    public function edit(int $id)
    {
        $csvType = CsvTypeTable::getInstance()->load($id);
        $editorView = new CsvEditorView($csvType);
        $templateView = new TemplateView($editorView);
        $response = $this->m_response->write($templateView);
        return $response;
    }
    
    
    /**
     * Display the web view for creating a rule group.
     */
    public function handleCreateSubmit()
    {
        $allPostPutVars = $this->m_request->getParsedBody();
        
        if (!isset($allPostPutVars['field_assignments']))
        {
            die("Missing required list of field assignments");
        }
        
        if (!isset($allPostPutVars['name']))
        {
            die("Missing required name of CSV");
        }
        
        $fields = $allPostPutVars['field_assignments'];
        
        if (isset($allPostPutVars['csv_type_id']))
        {
            $csvTypeId = intval($allPostPutVars['csv_type_id']);
            
            $csvType = CsvTypeTable::getInstance()->load($csvTypeId);
            $csvType->set_name($allPostPutVars['name']);
            
            # Remove the existing field assignments before replacing them.
            FieldAssignmentTable::getInstance()->deleteWhereAnd(array('csv_type_id' => $csvTypeId));
        }
        else
        {
            $newCsvTypeObject = CsvTypeTable::getInstance()->create(array(
                'name' => $allPostPutVars['name']
            ));
            
            $csvTypeId = $newCsvTypeObject->get_id();
        }
        
        
        foreach ($fields as $index => $field)
        {
            FieldAssignmentTable::getInstance()->create(array(
                'column_id' => $index,
                'csv_type_id' => $csvTypeId,
                'field_id' => $field->field_id
            ));
        }
        
        // Redirect back to the fields overview page where they will now see the new field
        $response = $this->m_response->withRedirect('/admin/field', 302);
        return $response;
    }
    
    
    /**
     * Handle the deletion of a field by ID 
     */
    public function delete(int $id)
    {
        CsvTypeTable::getInstance()->delete($id);
        $response = $this->m_response->withRedirect('/admin/csv-type', 302);
        return $response;
    }
}
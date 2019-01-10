<?php

/* 
 * 
 */

class ValidatorController extends AbstractController
{
    /**
     * Handle a service directly requesting that a file be validated. This will add
     * it to the queue and send a response saying so. THis will not just immediately start
     * validating).
     * @return type
     * @throws Exception
     */
    public function validate() : \Slim\Http\Response
    {
        // This is a signed request that has a 'token' in it 
        $requiredParams = array(
            'rule_group',
            'path', // this is a pre-signed request
            'web_hook',
        );
        
        
        $allPostPutVars = $this->m_request->getParsedBody();
        
        foreach ($requiredParams as $requiredParam)
        {
            if (!isset($allPostPutVars[$requiredParam]))
            {
                throw new Exception("Missing required parameter: {$requiredParam}", 400);
            }
        }
        
        if (!isset($allPostPutVars['csv_type']) && !isset($allPostPutVars['field_order']))
        {
            throw new Exception("csv_type or field_order need to be specified", 400);
        }
        
        $path = $allPostPutVars['path'];
        $webHook = $allPostPutVars['web_hook'];
        $ruleGroupId = intval($allPostPutVars['rule_group']);
        
        // Handle optional parameters.
        $language = 'en-gb';
        $delimiter = ',';
        
        if (isset($allPostPutVars['delimiter']))
        {
            $delimiter = $allPostPutVars['delimiter'];
        }
        
        if (isset($allPostPutVars['language']))
        {
            $language = $allPostPutVars['language'];
        }
        
        $fieldOrder = array();
        
        if (isset($allPostPutVars['field_order']))
        {
            $fieldOrder = json_decode($allPostPutVars['field_order'], true);
            
            if ($fieldOrder === NULL)
            {
                throw new Exception("Malformed field_order provided.", 400);
            }
        }
        else
        {
            $csvTypeID = intval($allPostPutVars['csv_type']);
            $fieldAssignments = FieldAssignmentTable::getInstance()->loadWhereAnd(array('csv_type_id' => $csvTypeID));
            
            if (count($fieldAssignments) == 0)
            {
                throw new Exception("Invalid csv_type specified", 400);
            }
            
            $sorter = function(FieldAssignment $a, FieldAssignment $b) {
                return $a->get_column_id() <=> $b->get_column_id();
            };
            
            usort($fieldAssignments, $sorter);
            
            $fieldOrder = array();
            
            foreach ($fieldAssignments as $assignment)
            {
                /* @var $assignment FieldAssignment */
                /* @var $field Field */
                $field = FieldTable::getInstance()->load($assignment->get_field_id());
                $fieldOrder[] = $field->get_name();
            }
        }
        
        $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
            RABBITMQ_HOST, 
            RABBITMQ_PORT, 
            RABBITMQ_USERNAME, 
            RABBITMQ_PASSWORD
        );
        
        $channel = $connection->channel();
        
        $validatorJob = new ValidationJob($path, $webHook, $ruleGroupId, $delimiter, $fieldOrder);
        $validatorJob->publish($channel);
        
        // Send back json response that the job was successfully added.
        $responseData = array(
            'result' => 'success',
            'message' => 'job successfully added.'
        );
        
        return SiteSpecific::getJsonResponse($responseData, $this->m_response);
    }
}
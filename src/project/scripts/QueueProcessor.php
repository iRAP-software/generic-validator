<?php

/* 
 * Script that continuously watches the a RabbitMQ queue for changes and will execute the requests.
 */

require_once(__DIR__ . '/../bootstrap.php');


class QueueProcessor
{
    private $m_host;
    private $m_user;
    private $m_password;
    private $m_port;
    private $m_queueName;
    private $m_sleepPeriod;
    
    
    public function __construct($rmqHost, $rmqUser, $rmqPassword, $rmqPort, $queueName, $sleepPeriod) 
    {
        $this->m_host = $rmqHost;
        $this->m_user = $rmqUser;
        $this->m_password = $rmqPassword;
        $this->m_port = $rmqPort;
        $this->m_queueName = $queueName;
        $this->m_sleepPeriod = $sleepPeriod;
    }
    
    
    public function run()
    {
        # DO NOT wrap this in a while (true) to run the script indefinitely. Let the process
        # start and stop and keep getting re-called by supervisor. This is simple and prevents
        # issues arising such as from the database going away or the possibility of a memory leak.
        try
        {
            $this->iteration();
            // End with a sleep so that we don't hammer the CPU loopign over and over again on an
            // empty queue
        } 
        catch (Exception $ex) 
        {
            $message = ENVIRONMENT . " Validator - QueueProcessor exception.";
            $context = array("exception" => $ex->getMessage());
            SiteSpecific::getLogger()->error($message, $context);
        }
        
        # Sleep for a period to prevent services/CPU getting hammered.
        sleep($this->m_sleepPeriod);
    }
    
    
    private function iteration()
    {
        $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
            $this->m_host, 
            $this->m_port, 
            $this->m_user, 
            $this->m_password
        );
        
        // Loop through the queu.
        $channel = $connection->channel();
        
        # Create the queue if it doesn't already exist.
        $channel->queue_declare(
            $queue = RABBITMQ_JOB_QUEUE_NAME,
            $passive = false,
            $durable = true,
            $exclusive = false,
            $auto_delete = false,
            $nowait = false,
            $arguments = null,
            $ticket = null
        );
        
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
        $self = $this;
        
        $jobCallback = function(PhpAmqpLib\Message\AMQPMessage $msg) use ($self) {
            
            $jobArray = json_decode($msg->getBody(), $arrayForm=true);
            
            if ($jobArray === null)
            {
                // The message was not a json string, ignore it.
                throw new Exception('Recieved validation job is not a json object: ' . $msg->getBody());
            }
            else
            {
                $requiredParams = array(
                    'path',
                    'web_hook',
                    'rule_group'
                );
                
                foreach ($requiredParams as $requiredParam)
                {
                    if (!isset($jobArray[$requiredParam]))
                    {
                        SiteSpecific::getLogger()->error('Validation job is missing required parameter: ' . $requiredParam);
                        // Send back the ack to rabbitmq. - In future put this into a new erroneous queue
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                        throw new Exception('Validation job is missing required parameter: ' . $requiredParam);
                    }
                }
                
                $fieldOrder = array();
                $delimiter = ',';
                
                if (isset($jobArray['field_order']))
                {
                    $fieldOrder = $jobArray['field_order'];
                }
                
                if (isset($jobArray['delimiter']))
                {
                    $delimiter = $jobArray['delimiter'];
                }
                
                SiteSpecific::getLogger()->debug("creating validation job: " . json_encode($jobArray));
                
                $validationJob = new ValidationJob(
                    $jobArray['path'], 
                    $jobArray['web_hook'], 
                    $jobArray['rule_group'], 
                    $delimiter,
                    $fieldOrder
                );
                
                // Send back the ack to rabbitmq
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                
                // Run the validation process.
                $self->runValidationJob($validationJob);
            }
        };
        
        $channel->basic_qos(null, 1, null);
        
        $channel->basic_consume(
            $queue = RABBITMQ_JOB_QUEUE_NAME,
            $consumer_tag = '',
            $no_local = false,
            $no_ack = false,
            $exclusive = false,
            $nowait = false,
            $jobCallback
        );
        
        // loop over the jobs in the queue and execute them all.
        try
        {
            while (count($channel->callbacks)) 
            {
                $channel->wait($allowed_methods=null, $nonBlocking=true, $timout=1);
            }
        } 
        catch (Exception $ex) 
        {
            // No more jobs in the queue. Quit out.
        }
        
        # Gracefully close down.
        $channel->close();
        $connection->close();
    }
    
    
    /**
     * Actually perform the validation job that needs to be done.
     */
    private function runValidationJob(ValidationJob $job)
    {
        SiteSpecific::getLogger()->debug(ENVIRONMENT . ' ' . SERVICE_NAME . ' - Validating a job');
        
        // Fetch the data from the url (which could be a signed S3 path) and write it to our 
        // local file.
        $prefix = time() . '_';
        $localFilepath = tempnam(sys_get_temp_dir(), $prefix);
        $data = file_get_contents($job->get_path());
        file_put_contents($localFilepath, $data);
        
        $ruleGroup = RuleGroupTable::getInstance()->load($job->get_ruleGroupID());
        
        $errors = array();
        $warnings = array();
        
        $resultContainer = new stdClass();
        $resultContainer->passed = true;
        $resultContainer->errors = array();
        $resultContainer->warnings = array();
        
        $callback = function(string $line) use ($job, $resultContainer, $ruleGroup) {
            static $humanRowCounter = 0;
            $humanRowCounter++;
            
            // convert the string line into an array
            $row = str_getcsv($line, $job->get_delimiter());
            
            if (count($job->get_fieldOrder()) > 0)
            {
                # requestor has provided the field order.
                $fieldOrder = $job->get_fieldOrder();
            }
            else
            {
                # load up the default field order.
                $fieldOrder = array(); 
                SiteSpecific::getLogger()->debug(ENVIRONMENT . ' ' . SERVICE_NAME . ' - todo load default field order');
                die("TODO - handle loading of default field order.");
            }
            
            if (count($fieldOrder) !== count($row))
            {
                $errorMessage = 
                    "Row " . $humanRowCounter . ": " . 
                    "Number of fields (" . count($row) . ") " . 
                    "did not match the expected number: " . count($fieldOrder);
                
                $resultContainer->errors = array_merge($resultContainer->errors, array($errorMessage));
            }
            else
            {
                if ($humanRowCounter > 1) // skip the first row which is string headers.
                {
                    // Use the field order to convert the row into assosciative one.
                    $assocRow = array();
                    
                    foreach ($row as $index => $value)
                    {
                        $fieldName = $fieldOrder[$index];
                        $assocRow[$fieldName] = $value;
                    }
                    
                    $result = $ruleGroup->evaluate($assocRow);
                    $resultContainer->errors = array_merge($resultContainer->errors, $result->getErrors());
                    $resultContainer->warnings = array_merge($resultContainer->warnings, $result->getWarnings());
                    
                    if ($result->getPassed() == FALSE)
                    {
                        $resultContainer->passed = $result->getPassed();
                    }
                }
            }
        };
        
        iRAP\CoreLibs\Filesystem::fileWalk($localFilepath, $callback);
        
        $passed = $resultContainer->passed;
        SiteSpecific::getLogger()->debug(ENVIRONMENT . ' ' . SERVICE_NAME . ' - validation result: ' . $passed);
        
        $resultString = 'passed';
        
        if ($passed)
        {
            $resultString = 'passed';
            
            if (count($resultContainer->warnings) > 0)
            {
                $resultString = 'warnings';
            }
        }
        else
        {
            $resultString = 'errors';
        }
        
        $parameters = array(
            'result' => $resultString,
            'errors' => $resultContainer->errors,
            'warnings' => $resultContainer->warnings
        );
        
        SiteSpecific::getLogger()->debug(ENVIRONMENT . ' ' . SERVICE_NAME . ' - webhook is: ' . $job->get_webHook());
        
        try
        {
            \iRAP\CoreLibs\Core::sendApiRequest($job->get_webHook(), $parameters);
        } 
        catch (Exception $ex) 
        {
            $msg = ENVIRONMENT . ' ' . SERVICE_NAME . 
                 ' - there wasn an exception sending API request to webhook';
            
            $context = array(
                "job" => $job,
                'exception' => $ex->getMessage()
            );
            
            SiteSpecific::getLogger()->debug($msg, $context);
        }
        
        //$context = array_merge($parameters, array("job" => $job));
        SiteSpecific::getLogger()->debug("Hitting webhook of validation job");
    }
}


$queueWatcher = new QueueProcessor(
    RABBITMQ_HOST, 
    RABBITMQ_USERNAME, 
    RABBITMQ_PASSWORD, 
    RABBITMQ_PORT, 
    RABBITMQ_JOB_QUEUE_NAME, 
    1
);

$queueWatcher->run();

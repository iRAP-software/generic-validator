<?php

/* 
 * Simple value object to represent a validation job that needs doing.
 */

class ValidationJob implements JsonSerializable
{
    private $m_path;
    private $m_webHook;
    private $m_ruleGroupID;
    private $m_fieldOrder;
    private $m_delimiter;
    
    
    public function __construct(string $path, string $webhook, int $rule_group_id, string $delimiter, array $fieldOrder = array())
    {
        $allowedDelimiters = ALLOWED_DELIMITERS;
        
        if (!in_array($delimiter, $allowedDelimiters))
        {
            throw new Exception("Invalid delimiter provided: " . $delimiter);
        }
        
        $this->m_path = $path;
        $this->m_webHook = $webhook;
        $this->m_ruleGroupID = $rule_group_id;
        $this->m_fieldOrder = $fieldOrder;
        $this->m_delimiter = $delimiter;
    }
    
    
    /**
     * Convert this object into a form that can be json encoded.
     * @return array
     */
    public function jsonSerialize() 
    {
        return array(
            'path' => $this->m_path,
            'web_hook' => $this->m_webHook,
            'rule_group' => $this->m_ruleGroupID,
            'field_order' => $this->m_fieldOrder,
            'delimiter' => $this->m_delimiter
        );
    }
    
    
    /**
     * Publish this job to the rabbitmq channel.
     * @param PhpAmqpLib\Channel\AMQPChannel $channel
     */
    public function publish(PhpAmqpLib\Channel\AMQPChannel $channel)
    {
        $msg = new \PhpAmqpLib\Message\AMQPMessage(
            json_encode($this, JSON_UNESCAPED_SLASHES),
            array('delivery_mode' => 2) # make message persistent
        );
        
        $channel->basic_publish($msg, '', RABBITMQ_JOB_QUEUE_NAME);
    }
    
    
    # Accessors
    public function get_path() { return $this->m_path; }
    public function get_webHook() { return $this->m_webHook; }
    public function get_ruleGroupID() { return $this->m_ruleGroupID; }
    public function get_fieldOrder() { return $this->m_fieldOrder; }
    public function get_delimiter() { return $this->m_delimiter; }
}
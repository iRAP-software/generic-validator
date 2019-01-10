<?php

class SiteSpecific
{
    /**
     * Helper function for returning a JSON response in SLIM.
     * @param array $data - the name/value pairs to send back in JSON
     * @param Slim\Http\Response $response - the response object to build from.
     * @param int $responseCode - the HTML response code, 200 for ok, 500 for errors etc.
     * @return \Slim\Http\Response $response - the new response object.
     */
    public static function getJsonResponse(Array $data, Slim\Http\Response $response, $responseCode=200)
    {
        $bodyJson = json_encode((object) $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $newResponse = $response->withStatus($responseCode)
                                ->withHeader("Content-Type", "application/json")
                                ->write($bodyJson);
        return $newResponse;
    }
    
    
    
    /**
     * Get the mysqli connection to the database. This will create a new connection if it 
     * doesn't already exist.
     * @return \mysqli - the mysqli connection to the validator database.
     */
    public static function getDb() : mysqli
    {
        static $connection = null;
        
        if ($connection == null)
        {
            $connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
            
            if ($connection->connect_error) 
            {
                die('Failed to connect to the database.');
            }
        }
        
        return $connection;
    }
    
    
    /**
     * Fetches the logger that the API uses. This is deliberately generic so that we can
     * swap it out if we ever decide to change systems. (e.g email logging or logging via
     * SMS etc).
     * @return iRAP\Logging\LoggerInterface
     */
    public static function getLogger()
    {
        static $s_logger = null;
        
        if ($s_logger === null)
        {
            try
            {
                $s_logger = new iRAP\RabbitmqLogger\RabbitmqQueueLogger(
                    RABBITMQ_HOST, 
                    RABBITMQ_USERNAME, 
                    RABBITMQ_PASSWORD, 
                    RABBITMQ_LOGS_QUEUE_NAME
                );
            } 
            catch (Exception $ex) 
            {
                // perhaps rabbitmq is down, proceed with an email logger for now.
                $emailer = SiteSpecific::getEmailer();
                
                $s_logger = new iRAP\Logging\EmailLogger(
                    $emailer, 
                    ADMIN_EMAILS, 
                    ENVIRONMENT . ' ' . SERVICE_NAME
                );
                
                $s_logger->error(ENVIRONMENT . ' API rabbitmq logging is not working. Please check the connection details.');
            }
        }
        
        return $s_logger;
    }
    
    
    /**
     * Retrieve an emailer to send emails with. Please don't use this for 
     * logging, use getLogger instead.
     * @return \iRAP\Emailers\EmailerInterface
     */
    public static function getEmailer()
    {
        static $s_emailer = null;
        
        if ($s_emailer === null)
        {
            $s_emailer = new iRAP\Emailers\PhpMailerEmailer(
                SMTP_HOST, 
                SMTP_USERNAME, 
                SMTP_PASSWORD, 
                'tls', 
                SMTP_FROM_EMAIL, 
                SMTP_FROM_NAME
            );
        }
        
        return $s_emailer;
    }
    
    
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    
    /**
     * Get the SSO client for interacting with the SSO.
     * @staticvar type $ssoClient
     * @return \iRAP\SsoClient\SsoClient
     */
    public static function getSsoClient()
    {
        static $ssoClient = null;
        
        if ($ssoClient === null)
        {
            $ssoClient = new iRAP\SsoClient\SsoClient(SSO_BROKER_ID, SSO_BROKER_SECRET);
        }
        
        return $ssoClient;
    }
}
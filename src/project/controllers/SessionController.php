<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SessionController extends AbstractController
{
    /**
     * Handle the user wanting to login and also acts as the webhook for the SSO
     * saying to log in.
     * If the user isn't logged in, create a SsoClient object and run the login() method.
     * The user's browser will be redirected to the SSO and then returned here with the user
     * credentials.
     */
    public function login()
    {
        $ssoClient = SiteSpecific::getSsoClient();
        
        if (SiteSpecific::isLoggedIn() === FALSE)
        {
            $ssoDetails = $ssoClient->login(array('path' => 'hello'));
            
            /* @var $ssoDetails \iRAP\SsoClient\SsoObject */
            if ($ssoClient->loginSuccessful())
            {
                /*
                 * The SsoClient provides a session id that can be used to identify the user, even from
                 * outside of the current session. This is useful when remotely destroying the session.
                 */
                if (session_status() === PHP_SESSION_ACTIVE)
                {
                    session_destroy();
                }
                
                session_id($ssoDetails->get_session_id());
                session_start();
                
                $_SESSION['user_id'] = $ssoDetails->get_user_id();
                $_SESSION['sso_expiry'] = $ssoDetails->get_sso_expiry();
                $returnData = $ssoDetails->get_return_data();
                
                $responseArray = array(
                    "status"  => "success",
                    "message" => "You have successfully logged in."
                );
                
                $response =  SiteSpecific::getJsonResponse($responseArray, $this->m_response);
            }
            else
            {
                $responseArray = array(
                    "status"  => "error",
                    "message" => "something went wrong with loggign in."
                );
                
                $response =  SiteSpecific::getJsonResponse($responseArray, $this->m_response, 500);
            }
        }
        else
        {
            $responseArray = array(
                "status"  => "success",
                "message" => "You are already logged in."
            );
            
            $response =  SiteSpecific::getJsonResponse($responseArray, $this->m_response);
        }
        
        if (false) // We need to handle this in the middleware rather than at login.
        {
            if (isset($_SESSION['sso_expiry']) && $_SESSION['sso_expiry'] < time())
            {
                /*
                 * When SSO expiry time is passed, the user should be redirected to the SSO, to keep its
                 * session alive. The user will be instantly returned to here.
                 */

                $ssoExpiry = $ssoClient->renewSSOSession($returnData);

                if ($ssoExpiry->get_sso_expiry())
                {
                    # The new SSO Expiry time should be saved, in order to trigger the next redirect. 
                    $_SESSION['sso_expiry'] = $ssoExpiry->get_sso_expiry();
                }
            }
        }
    }
    
    
    /**
     * Handle the user requesting to log out.
     */
    public function logout()
    {
        session_unset();
        $redirectUrl = SSO_SITE_HOSTNAME . '/logout';
        $response = $this->m_response->withRedirect($redirectUrl, 302);
        return $response;
    }
    
    
    /**
     * Handle the SSO sending a request to us to log a user/session out.
     * This will/should not be from the user (and we will check with signatures).
     */
    public function handleSsologout()
    {
        // Set the default response as an error.
        $response =  SiteSpecific::getJsonResponse(
            array('result' => 'error', 'message' => 'something went wrong with logging out.'), 
            $this->m_response, 
            500
        );
        
        $ssoClient = SiteSpecific::getSsoClient();
        
        //process the data the sso sent us e.g. ensure legit request and gets the seession ID
        $ssoLogout = $ssoClient->logoutWebhook(); 
        
        if (isset($ssoLogout->session_id))
        {
            $session_file = session_save_path() . '/sess_' . $ssoLogout->session_id;
            
            if (file_exists($session_file))
            {
                $session_destroyed = unlink($session_file);
                
                if ($session_destroyed)
                {
                    $responseArray = array(
                        "result"  => "success",
                        "message" => "Session destroyed."
                    );
                    
                    $response =  SiteSpecific::getJsonResponse($responseArray, $this->m_response);
                }
            }
        }
        
        return $response;
    }
}
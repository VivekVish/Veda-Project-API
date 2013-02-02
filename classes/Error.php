<?php

require_once('includes/defines.inc.php');
require_once('includes/defines.errors.inc.php');

class Error
{
    private static function convertErrorCodeToDebugMessage($errorCodeString)
    {
        return constant($errorCodeString);
    }
    
    private static function convertErrorCodeToUserMessage($errorCodeString)
    {
        return constant($errorCodeString);
    }
    
    public static function generateError($errorCode,$additionalMessage=null)
    {
        if(DEBUGON)
        {
            $errorCodeString = "DEBUGERROR_".strval($errorCode);
            $errorMessage = Error::convertErrorCodeToDebugMessage($errorCodeString);
            
            if(!empty($additionalMessage))
            {
                $errorMessage .= " ".$additionalMessage;
            }
            
            $messageArray = array("errorCode"=>$errorCodeString,"message"=>$errorMessage);
        }
        else
        {
            $errorCodeString = "ERROR_".strval($errorCode);
            $errorMessage = Error::convertErrorCodeToUserMessage($errorCodeString);
            $messageArray = array("message"=>$errorMessage);
        }
        
        die(json_encode($messageArray));
    }
}

?>

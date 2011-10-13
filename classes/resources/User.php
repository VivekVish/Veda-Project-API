<?php

class User
{
    ########################################################
	#### Member Variables ##################################
	########################################################
    private $idFound = false;
    private $username = null;
    private $provider = null;
    private $openId = null;
    private $status = null;
    
    ########################################################
	#### Constructor #######################################
	########################################################
    # Constructor
    public function __construct()
    {
    }
    
    ########################################################
	#### Helper functions for loading object ###############
	########################################################

	# Load from path
    public function loadFromUri($uri)
    {
        $uriArr = explode("/",trim($uri,"/"));
        $openId = preg_replace('/FORWARDSLASHCODE/','/',urldecode($uriArr[2]));

        if($uriArr[1]=="username")
        {
            $query = sprintf("SELECT username FROM usernames WHERE LOWER(username)='%s'",pg_escape_string(strtolower($openId)));
        }
        else
        {
            if($uriArr[1]=="yahoo!")
            {
                $provider="yahoo";
            }
            else
            {
                $provider=$uriArr[1];
            }
            
            $query = sprintf("SELECT usernames.username AS username, user_status.status AS status, providers.name AS provider FROM users 
                                LEFT JOIN providers ON (users.provider_id = providers.provider_id)
                                LEFT JOIN user_status ON (users.status_id = user_status.status_id)
                                LEFT JOIN usernames ON (users.user_id = usernames.id)
                                WHERE providers.name = '%s' AND users.open_id='%s'",pg_escape_string($provider), pg_escape_string($openId));
        }
        
        $usernameArray = $GLOBALS['transaction']->query($query);
        
        if($usernameArray=='none')
        {
            $this->idFound = false;
        }
        else
        {
            $this->idFound = true;
            if($uriArr[1]!="username")
            {
                $this->username = $usernameArray[0]['username'];
                $this->status = $usernameArray[0]['status'];
                $this->provider = $usernameArray[0]['provider'];
                if($this->provider=="yahoo!")
                {
                    $this->provider="yahoo";
                }
            }
        }
        
        return true;
    }
    
    public function loadFromPayload($payload,$uri)
    {
        
        $uriArr = explode("/",trim($uri,"/"));
        $this->username =  pg_escape_string($uriArr[2]);
        $this->openId = preg_replace('/FORWARDSLASHCODE/','/',urldecode($payload->identity));
        $this->provider = $payload->provider;
        if($this->provider=="yahoo!")
        {
            $this->provider="yahoo";
        }
        
        if(isset($payload->status))
        {
            $this->status = $payload->status;
        }
        
        return true;
    }
    
    public function buildXML()
    {
        if($this->idFound)
        {
            return "<user><id>{$this->username}</id><status>{$this->status}</status></user>";
        }
        else
        {
            return "<user>User Not Found.</user>";
        }
    }
    
    ########################################################
	#### Database interface functions ######################
	########################################################
    
    public function save()
    {
        // Get Provider Id
        $query = sprintf("SELECT provider_id FROM providers WHERE name='%s'", pg_escape_string($this->provider));
        $this->providerIdArray = $GLOBALS['transaction']->query($query,84);

        // Check if username is taken
        $query = sprintf("SELECT username FROM usernames WHERE LOWER(username)='%s'",pg_escape_string(strtolower($this->username)));
        $alreadyExists = $GLOBALS['transaction']->query($query)=='none' ? false : true;
        
        if(!$alreadyExists)
        {
            $query = sprintf("INSERT INTO usernames VALUES ('%s')", pg_escape_string($this->username));
            $GLOBALS['transaction']->query($query,83);
        }
        
        // Check if openid is taken
        $query = sprintf("SELECT usernames.username FROM users LEFT JOIN usernames ON (usernames.id=users.user_id) WHERE users.open_id='%s'",pg_escape_string($this->openId));
        $alreadyExists = $GLOBALS['transaction']->query($query)=='none' ? false : true;
        
        if($alreadyExists)
        {
            Error::generateError(85);
        }
        
        //Get User Id
        $query = sprintf("SELECT id from usernames WHERE username='%s'", pg_escape_string($this->username));
        $userIdArray = $GLOBALS['transaction']->query($query,86);

        $userId = $userIdArray[0]['id'];
        
        // Insert username
        if(isset($this->status))
        {
            //Get Status Id
            $query = sprintf("SELECT status_id from user_status WHERE status='%s'",pg_escape_string($this->status));
            $statusIdArray = $GLOBALS['transaction']->query($query,87);
            
            $statusId = $statusIdArray[0]['status_id'];
            
            $query = sprintf("INSERT INTO users (user_id,provider_id,open_id,status_id) VALUES ('%s','%s','%s','%s')",  pg_escape_string($userId),  pg_escape_string($this->providerIdArray[0]['provider_id']), pg_escape_string($this->openId), pg_escape_string($statusId));
            $GLOBALS['transaction']->query($query,88);
        }
        else
        {           
            $query = sprintf("INSERT INTO users (user_id,provider_id,open_id) VALUES ('%s','%s','%s','%s')",  pg_escape_string(userId),  pg_escape_string($this->providerIdArray[0]['provider_id']), pg_escape_string($this->openId));
            $GLOBALS['transaction']->query($query,88);
        }
        
        return true;
    }
    
    ########################################################
	### Static functions ###################################
	########################################################
    public static function usernameToId($username)
    {
        $query = sprintf("SELECT id FROM usernames WHERE username='%s'",pg_escape_string($username));
        $userIdArray = $GLOBALS['transaction']->query($query,89);

        return $userIdArray[0]['id'];
    }
    
    ########################################################
	### Getters and Setters ################################
	########################################################
}

?>

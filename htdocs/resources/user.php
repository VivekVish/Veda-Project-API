<?php
    require_once("classes/resources/User.php");
    $user = new User();
    
    switch(strtolower($this->request->getMethod()))
	{
        case 'get':
            if($user->loadFromUri($this->request->getUri()))
            {
                $this->response->setPayload($user->buildXML());
                $this->setStatus(true);
            }
            else
            {
                Error::generateError(90);
            }
            break;
        case 'put': case 'post':
            $payload = json_decode($this->request->getPayload());
            if($user->loadFromPayload($payload,$this->request->getUri()))
            {
                if($user->save())
                {
                    $this->response->setPayload("Success.");
                    $this->setStatus(true);
                }
                else
                {
                    
                }
            }
            else
            {
                Error::generateError(91);
            }
            break;
        case 'delete':
            
            break;
    }
?>

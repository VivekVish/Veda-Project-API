<?php
    require_once("classes/resources/User.php");
    require_once("classes/resources/Image.php");
    $image = new Image();
    
   
    
    switch (strtolower($this->request->getMethod()))
    {
        case 'get':
            if($image->loadFromUri($this->request->getUri()))
            {
                $this->response->setPayload($image->buildJSON());
                $this->setStatus(true);
                break;
            }
            break;
        case 'put': case 'post':
            $payload = json_decode($this->request->getPayload());
            
            //running the following function returns true if all needed info (username etc. is present)
            if($image->loadFromPayload($payload, $this->request->getUri()))
            {
                //try to save image info to db
                $imageId = $image->save(User::usernameToId($payload->username));
                if($imageId !== false)//!= means are they not the same value, !== means are they not the same value OR not the same type
                {
                    
                    //if image save succeeded, set payload to "success"
                    $this->response->setPayload(json_encode(array("status"=>"Success.","imageId"=>$imageId)));
                    $this->setStatus(true);
                    break; ///since all this code is copied into Api.php (api.php?) "this" refers to the api instance and "break" breaks out of the switch between get, post/put, and delete
                }
                else
                {
                    Error::generateError(32);
                }
            }
            else 
            {
                Error::generateError(33);
            }
            break;
        case 'delete':
            if($image->loadFromUri($this->request->getUri()))//loads image
            {
                if($image->delete())
		{
			$this->setStatus(true);
			break;
		}
               
            }
            
            else
            {
                $this->setStatus(false);
                break;
            }
    }
?>

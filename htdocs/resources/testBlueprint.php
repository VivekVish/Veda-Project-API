<?php
 require_once("classes/resources/User.php");
    require_once("classes/resources/TestBlueprint.php");
    $testBlueprint = new TestBlueprint();

    switch (strtolower($this->request->getMethod()))
    {
        case 'get':
            
            if($testBlueprint->loadFromUri($this->request->getUri())){
                $testBlueprint->buildJSON();
                $this->response->setPayload($testBlueprint->getJSON());
                $this->setStatus(true);
                break;
           }
           break;
            
        case 'put': case 'post':
             $payload = json_decode($this->request->getPayload());
            
            //running the following function returns true if all needed info (username etc. is present)
            if($testBlueprint->loadFromPayload($payload, $this->request->getUri()))
            {
                
                
            }
        case 'delete':
       
    }
?>

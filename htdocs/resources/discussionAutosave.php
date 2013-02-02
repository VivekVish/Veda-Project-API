<?php
    require_once("classes/resources/ContentAutosave.php");
    $discussionAutosave = new ContentAutosave();
    
    switch (strtolower($this->request->getMethod()))
    {
        case "get":
            if($discussionAutosave->loadFromUri($this->request->getUri()))
            {
                $discussionAutosave->buildJSON(); 
                $this->response->setPayload($discussionAutosave->getJSON());
                $this->response->setContentType("text/xml");
                $this->setStatus(true);
                break;
            }
            $this->setStatus(false);
            break;
        case "put": case "post":
            $payload = json_decode($this->request->getPayload());
            
            if ($discussionAutosave->loadFromPayload($payload,$this->request->getURI()))
            {
                if ($discussionAutosave->save())
                {
                    $this->response->setPayload("Success.");
                    $this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
                Error::generateError(22);
                break;
            }
            break;
        case "delete":
            break;
    }
?>

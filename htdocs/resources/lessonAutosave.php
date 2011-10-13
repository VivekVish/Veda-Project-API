<?php
    require_once("classes/resources/ContentAutosave.php");
    $lessonAutosave = new ContentAutosave();
    
    switch (strtolower($this->request->getMethod()))
    {
        case "get":
            if($lessonAutosave->loadFromUri($this->request->getUri()))
            {
                $lessonAutosave->buildJSON(); 
                $this->response->setPayload($lessonAutosave->getJSON());
                $this->response->setContentType("text/xml");
                $this->setStatus(true);
                break;
            }
            $this->setStatus(false);
            break;
        case "put": case "post":
            $payload = json_decode($this->request->getPayload());
            
            if ($lessonAutosave->loadFromPayload($payload,$this->request->getURI()))
            {
                if ($lessonAutosave->save())
                {
                    $this->response->setPayload("Success.");
                    $this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
                Error::generateError(58);
                break;
            }
            break;
        case "delete":
            break;
    }
?>

<?php
    require_once("classes/resources/Material.php");
    require_once("classes/resources/RevisionHistory.php");
    $lessonId = Material::URIToId($this->request->getUri(),"lesson");
    $lessonHistory = new RevisionHistory("lesson_history","lesson",$lessonId);
    
    switch (strtolower($this->request->getMethod()))
    {
        case "get":
            $history = $lessonHistory->getHistory();
            if($history!==false)
            {
                $this->response->setPayload(json_encode($history));
                $this->setStatus(true);
            }
            else
            {
                Error::generateError(59);
            }
            break;
        case "post": case "put":
            break;
        case "delete":
            break;
    }
?>
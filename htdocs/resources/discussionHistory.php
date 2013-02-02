<?php
    require_once("classes/resources/Material.php");
    require_once("classes/resources/RevisionHistory.php");
    $discussionId = Material::URIToId($this->request->getUri(),"discussion");
    if(!is_null($discussionId))
    {
        $discussionHistory = new RevisionHistory("discussion_history","discussion",$discussionId);
    }
    
    switch (strtolower($this->request->getMethod()))
    {
        case "get":
            if(!is_null($discussionId))
            {
                $history = $discussionHistory->getHistory();
                if($history!==false)
                {
                    $this->response->setPayload(json_encode($history));
                    $this->setStatus(true);
                }
                else
                {
                    Error::generateError(94);
                }
            }
            else
            {
                $this->response->setPayload(json_encode(array()));
                $this->setStatus(true);
            }
            break;
        case "post": case "put":
            break;
        case "delete":
            break;
    }
?>

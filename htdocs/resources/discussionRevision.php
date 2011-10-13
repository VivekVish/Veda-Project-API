<?php
    require_once("classes/resources/RevisionHistory.php");
    $discussionRow = new RevisionRow("discussion_history","discussion");

    switch (strtolower($this->request->getMethod()))
    {
        case "get":
            if($discussionRow->loadFromUri($this->request->getUri()))
            {
                $this->response->setPayload($discussionRow->getJSON());
                $this->setStatus(true);
                break;
            }
            $this->setStatus(false);
            break;
        case "put": case "post":
            break;
        case "delete":
            break;
    }
?>

<?php
    require_once("classes/resources/RevisionHistory.php");
    $lessonRow = new RevisionRow("lesson_history","lesson");

    switch (strtolower($this->request->getMethod()))
    {
        case "get":
            $uriArr = explode("/",trim($this->request->getUri(),"/"));
            $compareTo = array_pop($uriArr);
            if($lessonRow->loadFromUri(join("/",$uriArr),$compareTo))
            {
                $this->response->setPayload($lessonRow->getJSON());
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
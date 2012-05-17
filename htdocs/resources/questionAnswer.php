<?php

require_once("classes/resources/TestBlueprint.php");
require_once("classes/resources/TempQuestion.php");
require_once("classes/resources/User.php");

$questionBlueprint = new TempQuestion();
$uriArr = explode("/",trim($this->request->getUri(),"/"));

switch (strtolower($this->request->getMethod()))
{
    case 'get':
        break;
    case 'post':
    case 'put':
        $payloadArray = json_decode($this->request->getPayload());
        if($questionBlueprint->loadFromId($uriArr[2]))
        {
            if($questionBlueprint->submitAnswer($payloadArray->answerId,User::usernameToId($payloadArray->username)))
            {
                $this->response->setPayload("Success.");
                $this->response->setContentType("text/xml");
                $this->setStatus(true);
            }
        }
        break;
    case 'delete':
        break;
}
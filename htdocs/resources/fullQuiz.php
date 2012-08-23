<?php

require_once("classes/resources/TestBlueprint.php");
require_once("classes/resources/TempQuestion.php");
require_once("classes/resources/User.php");
$quizBlueprint = new TestBlueprint();
$uriArr = explode("/",trim($this->request->getUri(),"/"));

switch (strtolower($this->request->getMethod()))
{
    case 'get':
        if($quizBlueprint->loadFromUri($this->request->getUri()))
        {
            $questions = array();
            foreach($quizBlueprint->getChildData() as $question)
            {
                $tempQuestion = new TempQuestion();
                $tempQuestion->loadFromId($question['id']);
                $tempQuestion->buildJSON();
                $questions[] = json_decode($tempQuestion->getJSON());
            }
            
            $this->response->setPayload(json_encode($questions));
            $this->response->setContentType("text/xml");
			$this->setStatus(true);
        }
        break;
    case 'post':
    case 'put':
        
        break;
    case 'delete':

        break;
}
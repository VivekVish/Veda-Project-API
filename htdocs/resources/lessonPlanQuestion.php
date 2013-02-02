<?php

require_once("classes/resources/TestBlueprint.php");
require_once("classes/resources/TempQuestion.php");
require_once("classes/resources/LessonPlanLesson.php");
require_once("classes/resources/User.php");
    
$quizBlueprint = new TestBlueprint();
$lessonPlanLesson = new LessonPlanLesson();
$uriArr = explode("/",trim($this->request->getUri(),"/"));

switch (strtolower($this->request->getMethod()))
{
    case 'get':
        if($lessonPlanLesson->loadFromUri($this->request->getUri()))
        {
            $lessonId = $lessonPlanLesson->getLessonId();
            if($quizBlueprint->loadFromId($lessonId))
            {
                $submittedAnswers = array();
                foreach($quizBlueprint->getChildData() as $question)
                {
                    $tempQuestion = new TempQuestion();
                    $tempQuestion->loadFromId($question['id']);
                    $answer = $tempQuestion->getSubmittedAnswer(User::usernameToId($uriArr[6]));
                    if($answer===false)
                    {
                        array_push($submittedAnswers,array("id"=>$question['id'],"answered"=>false));
                    }
                    else
                    {
                        $answer['id']=$question['id'];
                        $answer['answered']=true;
                        array_push($submittedAnswers,$answer);
                    }
                }

                $this->response->setPayload(json_encode($submittedAnswers));
                $this->response->setContentType("text/xml");
                $this->setStatus(true);
            }
        }
        break;
    case 'post':
    case 'put':
        
        break;
    case 'delete':
        if($lessonPlanLesson->loadFromUri($this->request->getUri()))
        {
            $lessonId = $lessonPlanLesson->getLessonId();
            if($quizBlueprint->loadFromId($lessonId))
            {
                $quizBlueprint->deleteUserAnswers(User::usernameToId($uriArr[6]));
                $this->response->setPayload("Success.");
                $this->response->setContentType("text/xml");
                $this->setStatus(true);
            }
        }
        break;
}
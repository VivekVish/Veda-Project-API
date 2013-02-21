<?php
    require_once("classes/resources/LessonPlanLesson.php");
    require_once("classes/resources/User.php");
    $lessonPlanLesson = new LessonPlanLesson();

    switch (strtolower($this->request->getMethod()))
    {
        case 'get':
            break;
        case 'put':
        case 'post':
            $uriArr = explode("/",trim($this->request->getUri(),"/"));
            if($lessonPlanLesson->loadFromUri($this->request->getUri()))
            {
                if($lessonPlanLesson->addAddition($uriArr[5]))
                {
                    $this->response->setPayload("Success.");
                    $this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
            }
            break;
        case 'delete':
            $uriArr = explode("/",trim($this->request->getUri(),"/"));
            if($lessonPlanLesson->loadFromUri($this->request->getUri()))
            {
                if($lessonPlanLesson->dropAddition($uriArr[5]))
                {
                    $this->response->setPayload("Success.");
                    $this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
            }
            break;
    }
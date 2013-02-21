<?php
    require_once("classes/resources/LessonPlanLesson.php");
    require_once("classes/resources/User.php");
    $lessonPlanLesson = new LessonPlanLesson();
    
    switch (strtolower($this->request->getMethod()))
    {
        case 'get':
            $uriArr = explode("/",trim($this->request->getUri(),"/"));
            if($lessonPlanLesson->loadFromUri($this->request->getUri()))
            {
                if($lessonPlanLesson->loadAddition($uriArr[5]))
                {
                    $lessonPlanLesson->buildAdditionJSON();
                    $this->response->setPayload($lessonPlanLesson->getAdditionJSON());
                    $this->setStatus(true);
                    break;
                }
            }
            break;
        case 'put':
        case 'post':
            $uriArr = explode("/",trim($this->request->getUri(),"/"));
            if($lessonPlanLesson->loadFromUri($this->request->getUri()))
            {
                if($lessonPlanLesson->loadAddition($uriArr[5]))
                {
                    $payload = json_decode($this->request->getPayload());
                    if($lessonPlanLesson->loadCustomAdditionByPayload($payload))
                    {
                        if($lessonPlanLesson->saveCustomAddition())
                        {
                            $this->response->setPayload("Success.");
                            $this->response->setContentType("text/xml");
                            $this->setStatus(true);
                            break;
                        }
                    }
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
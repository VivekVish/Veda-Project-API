<?php
    require_once("classes/resources/UserLessonPlan.php");
    
    $userLessonPlan = new UserLessonPlan();
    
    switch(strtolower($this->request->getMethod()))
    {
        case 'get':
            if($userLessonPlan->loadFromUri($this->request->getUri()))
            {
                if($userLessonPlan->buildJSON())
                {
                    $this->response->setPayload($userLessonPlan->getJSON());
                    $this->setStatus(true);
                    break;
                }
            }
            $this->setStatus(false);
            break;
        case 'put':
        case 'post':
            $payload = json_decode($this->request->getPayload());
            
            if($userLessonPlan->loadFromPayload($payload,$this->request->getUri()))
            {
                if($userLessonPlan->save())
                {
                    $this->response->setPayload(json_encode(array("status"=>"Success.","id"=>$userLessonPlan->getId())));
                    $this->setStatus(true);
                    break;
                }
            }
            $this->setStatus(false);
            break;
        case 'delete':
            if($userLessonPlan->loadFromUri($this->request->getUri()))
            {
                if($userLessonPlan->delete())
                {
                    $this->response->setPayload("Success.");
                    $this->setStatus(true);
                    break;
                }
            }
            $this->setStatus(false);
            break;
    }
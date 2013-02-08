<?php
    require_once("classes/resources/LessonPlanManager.php");
    require_once("classes/resources/User.php");
    $lessonPlanManager = new LessonPlanManager();
    
    switch(strtolower($this->request->getMethod()))
    {
        case 'get':
            $uriArr = split("/",trim($this->request->getUri(),"/"));
            $lessonPlans = json_encode(LessonPlanManager::getLessonPlansByManager(User::usernameToId($uriArr[2])));
            $this->response->setPayload($lessonPlans);
            $this->response->setContentType("text/xml");
            $this->setStatus(true);
            break;
        case 'put':
        case 'post':
            $uriArr = split("/",trim($this->request->getUri(),"/"));

            if($lessonPlanManager->loadFromPayload(json_decode($this->request->getPayload())))
            {
                if($lessonPlanManager->save(User::usernameToId($uriArr[2])))
                {
                    $this->response->setPayload(json_encode(array("id"=>$lessonPlanManager->getId())));
                                        $this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
            }
            $this->setStatus(false);
            break;
        case 'delete':
            break;
    }
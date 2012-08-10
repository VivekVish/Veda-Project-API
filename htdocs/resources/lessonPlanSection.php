<?php
    require_once("classes/resources/LessonPlanSection.php");
    require_once("classes/resources/User.php");
    $lessonPlanSection = new LessonPlanSection();
    
    switch (strtolower($this->request->getMethod()))
    {
        case 'get':
            if($lessonPlanSection->loadFromUri($this->request->getUri()))
            {
                $this->response->setPayload($image->buildJSON());
                $this->setStatus(true);
                break;
            }
            break;
        case 'put':
        case 'post':
            $payload = json_decode($this->request->getPayload());
            if(isset($payload->name))
            {
                if($lessonPlanSection->loadFromUri($this->request->getUri()))
                {
                    if($lessonPlanSection->rename($payload->name,User::usernameToId($payload->username)))
                    {
                        $this->response->setPayload("Success.");
                        $this->setStatus(true);
                        break;
                    }
                }
            }
            else
            {
                if($lessonPlanSection->loadFromPayload($payload,$this->request->getUri()))
                {
                    if($lessonPlanSection->save(User::usernameToId($payload->username)))
                    {
                        $this->response->setPayload("Success.");
                        $this->setStatus(true);
                        break;
                    }
                }
            }
            $this->setStatus(false);
            break;
        case 'delete':
            if($lessonPlanSection->loadFromUri($this->request->getUri()))
            {
                if($lessonPlanSection->delete())
                {
                    $this->response->setPayload("Success.");
					$this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
            }
            break;
    }
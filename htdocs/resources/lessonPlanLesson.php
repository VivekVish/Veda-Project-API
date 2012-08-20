<?php
    require_once("classes/resources/LessonPlanLesson.php");
    require_once("classes/resources/User.php");
    $lessonPlanLesson = new LessonPlanLesson();
    
    switch (strtolower($this->request->getMethod()))
    {
        case 'get':
            if($lessonPlanLesson->loadFromUri($this->request->getUri()))
            {
                $this->response->setPayload($image->buildJSON());
                $this->setStatus(true);
                break;
            }
            break;
        case 'put':
        case 'post':            
            if($lessonPlanLesson->loadFromPayload(json_decode($this->request->getPayload()),$this->request->getUri()))
            {
                if($lessonPlanLesson->save())
                {
                    $this->response->setPayload("Success.");
                    $this->setStatus(true);
                    break;
                }
            }
            $this->setStatus(false);
            break;
        case 'delete':
            if($lessonPlanLesson->loadFromUri($this->request->getUri()))
            {
                if($lessonPlanLesson->delete())
                {
                    $this->response->setPayload("Success.");
					$this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
            }
            break;
    }
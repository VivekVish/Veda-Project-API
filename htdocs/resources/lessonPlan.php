<?php
    require_once("classes/resources/LessonPlanManager.php");
    require_once("classes/resources/User.php");
    $lessonPlanManager = new LessonPlanManager();
    
    switch(strtolower($this->request->getMethod()))
	{
		case 'get':
            
			break;
		case 'put':
		case 'post':
            
			break;
		case 'delete':
            if($lessonPlanManager->loadFromUri($this->request->getUri()))
            {
                if($lessonPlanManager->delete())
                {
                    $this->response->setPayload("Success.");
					$this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
            }
			break;
	}
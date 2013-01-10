<?php
    require_once("classes/resources/LessonPlanSection.php");
    require_once("classes/resources/User.php");
    $lessonPlanSection = new LessonPlanSection();
    
    switch (strtolower($this->request->getMethod()))
    {
        case 'get':
			break;
		case 'put':
		case 'post':
			$payload = json_decode($this->request->getPayload());
			if($lessonPlanSection->loadFromUri($payload->oldPath))
			{
				if($lessonPlanSection->setPosition((int)$payload->newOrder,User::usernameToId($payload->username)))
				{
					$this->response->setPayload("Success.");
					$this->response->setContentType("text/xml");
					$this->setStatus(true);
					break;
				}
			}
			break;
		case 'delete':
			break;
    }
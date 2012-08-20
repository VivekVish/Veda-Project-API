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
			$payload = json_decode($this->request->getPayload());
			if($lessonPlanLesson->loadFromUri($payload->oldPath))
			{
				if($lessonPlanLesson->setPosition($payload->newPath,(int)$payload->newOrder,$payload->oldPath))
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
<?php
    require_once("classes/resources/User.php");
    
    switch (strtolower($this->request->getMethod()))
    {
        case 'get':
            require_once("classes/resources/Course.php");
            $course = new Course();
            
            if ($course->loadFromUri($this->request->getUri()))
            {
                $this->response->setPayload($course->getDeletedLessons());
                $this->setStatus(true);
                break;
            }
            $this->setStatus(false);
            break;
        case 'put':
        case 'post':
            require_once("classes/resources/Lesson.php");
            $payload = json_decode($this->request->getPayload());
            $successfulRecoveries = Lesson::recoverDeletedLessons($this->request->getUri(),$payload->lessonIds,User::usernameToId($payload->username));
            $this->response->setPayload(json_encode($successfulRecoveries));
            $this->setStatus(true);
            break;
        case 'delete':
            break;
    }
?>

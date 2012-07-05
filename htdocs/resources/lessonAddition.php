<?php

require_once("classes/resources/LessonAddition.php");
require_once("classes/resources/User.php");
$uri = strtolower($this->request->getUri());
$uriArr = split("/",trim($uri,"/"));
$lessonAddition = new LessonAddition($uriArr[LESSON_ADDITION_INDEX]);

switch (strtolower($this->request->getMethod()))
{
    case 'get':
        if ($lessonAddition->loadFromUri($this->request->getUri()))
		{
            $lessonAddition->buildJSON();
			$this->response->setPayload($lessonAddition->getJSON());
            $this->setStatus(true);
			break;
        }
        $this->setStatus(false);
        break;
    case 'post':
    case 'put':
        $payload = json_decode($this->request->getPayload());
        if($lessonAddition->loadFromPayload($payload,$this->request->getUri()))
        {
            if($lessonAddition->save(User::usernameToId($payload->username)))
            {
                $this->response->setPayload("Success.");
                $this->setStatus(true);
                break;
            }
        }
        $this->setStatus(false);
        break;
    case 'delete':
        break;
}
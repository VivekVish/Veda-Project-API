<?php

require_once("classes/resources/Lesson.php");
require_once("classes/resources/Ilo.php");
require_once("classes/resources/User.php");
$lesson = new Lesson();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
        if ($lesson->loadFromUri($this->request->getUri()))
		{
			$lesson->buildJSON();
			$this->response->setPayload($lesson->getJSON());
            $this->response->setContentType("text/xml");
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
	case 'put':
	case 'post':
		$payload = json_decode($this->request->getPayload());

        if($payload->newLesson)
        {
            if($lesson->loadFromUri($this->request->getURI(),false))
            {
                Error::generateError(53);
            }
            else
            {
                if ($lesson->loadFromPayload($payload,$this->request->getURI()))
                {
                    if ($lesson->save(User::usernameToId($payload->username)))
                    {
                        $this->response->setPayload("Success.");
                        $this->response->setContentType("text/xml");
                        $this->setStatus(true);
                        break;
                    }
                    Error::generateError(54);
                    break;
                }
            }
        }
        else if(isset($payload->content))
        {
            if ($lesson->loadFromPayload($payload,$this->request->getURI()))
            {
                if(!isset($payload->notes))
                {
                    $payload->notes=null;
                }
                
                if ($lesson->save(User::usernameToId($payload->username),$payload->notes))
                {
                    $this->response->setPayload("Success.");
                    $this->response->setContentType("text/xml");
                    $this->setStatus(true);
                    break;
                }
                Error::generateError(55);
                break;
            }
        }
        else
        {
                if($lesson->loadFromUri($this->request->getURI()))
                {
                    if($lesson->rename($payload->name,User::usernameToId($payload->username)))
                    {
                        $this->response->setPayload("Success.");
                        $this->response->setContentType("text/xml");
                        $this->setStatus(true);
                        break;
                    }
                    else
                    {
                        Error::generateError(56);
                    }
                }
        }
		Error::generateError(57);
		$this->response->setContentType("text/xml");
		$this->setStatus(false);
		break;
	case 'delete':
		$lesson->loadFromUri($this->request->getURI());
        $payload = json_decode($this->request->getPayload());

		if ($lesson->delete(User::usernameToId($payload->username)))
		{
            $this->response->setPayload("Success.");
            $this->response->setContentType("text/xml");
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
}
?>

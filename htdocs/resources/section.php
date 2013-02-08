<?php

require_once("classes/resources/Section.php");
require_once("classes/resources/User.php");
$section = new Section();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
            $section->loadFromUri($this->request->getUri());
            $section->buildJSON();
            $payload = $section->getJSON();
            if (!empty($payload))
            {
                $this->response->setPayload($payload);
                $this->setStatus(true);
                break;
            }
            $this->setStatus(false);
            break;
	case 'put':
	case 'post':
            $payload = json_decode($this->request->getPayload());
            if(isset($payload->name))
            {
                if($section->loadFromUri($this->request->getUri()))
                {
                    if($section->rename($payload->name,User::usernameToId($payload->username)))
                    {
                        $this->response->setPayload("Success.");
                        $this->setStatus(true);
                        break;
                    }
                }
            }
            else
            {
                if($section->loadFromUri($this->request->getUri(),false))
                {
                    Error::generateError(74);
                }
                else if ($section->loadFromPayload($payload,$this->request->getUri()))
                {
                    if ($section->save(User::usernameToId($payload->username)))
                    {
                        $this->response->setPayload("Success.");
                        $this->setStatus(true);
                        break;
                    }
                    else
                    {
                        Error::generateError(75);
                    }
                }
                else
                {
                    Error::generateError(76);
                }
            }
            $this->setStatus(false);
            break;
	case 'delete':
		if ($section->loadFromUri($this->request->getUri()))
		{
                    if($section->delete())
                    {
                        $this->response->setPayload("Success.");
                        $this->setStatus(true);
                        break;
                    }
		}
		$this->setStatus(false);
		break;
}
?>

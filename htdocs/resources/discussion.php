<?php

require_once("classes/resources/Discussion.php");
require_once("classes/resources/Ilo.php");
require_once("classes/resources/User.php");
$discussion = new Discussion();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
        if ($discussion->loadFromUri($this->request->getUri()))
		{
			$discussion->buildJSON();
			$this->response->setPayload($discussion->getJSON());
            $this->response->setContentType("text/xml");
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
	case 'put':
	case 'post':
		$payload = json_decode($this->request->getPayload());

        if ($discussion->loadFromPayload($payload,$this->request->getURI()))
        {
            if(!isset($payload->notes))
            {
                $payload->notes=null;
            }
            if ($discussion->save(User::usernameToId($payload->username),$payload->notes))
            {
                $this->response->setPayload("Success.");
                $this->response->setContentType("text/xml");
                $this->setStatus(true);
                break;
            }
            Error::generateError(20);
            break;
        }

		Error::generateError(93);
		$this->response->setContentType("text/xml");
		$this->setStatus(false);
		break;
	case 'delete':
		$discussion->loadFromUri($this->request->getURI());
		if ($discussion->delete())
		{
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
}
?>

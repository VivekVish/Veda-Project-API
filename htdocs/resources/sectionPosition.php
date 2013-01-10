<?php
    require_once("classes/resources/Section.php");
    require_once("classes/resources/User.php");
	$section = new Section();

	switch(strtolower($this->request->getMethod()))
	{
		case 'get':
			break;
		case 'put':
		case 'post':
			$payload = json_decode($this->request->getPayload());
			if($section->loadFromUri($payload->oldPath))
			{
				if($section->setPosition($payload->newPath,$payload->newOrder,$payload->oldPath,User::usernameToId($payload->username)))
				{
					$this->response->setPayload("Success.");
					$this->response->setContentType("text/xml");
					$this->setStatus(true);
					break;
				}
				Error::generateError(77);
			}
			Error::generateError(78);
			break;
		case 'delete':
			break;
	}
?>

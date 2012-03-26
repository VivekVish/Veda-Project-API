<?php
	require_once("classes/resources/Lesson.php");
    require_once("classes/resources/User.php");
	$lesson = new Lesson();

	switch(strtolower($this->request->getMethod()))
	{
		case 'get':
			break;
		case 'put':
		case 'post':
			$payload = json_decode($this->request->getPayload());
			if($lesson->loadFromUri($payload->oldPath."content/"))
			{
				if($lesson->setPosition($payload->newPath,$payload->newOrder,$payload->oldPath,User::usernameToId($payload->username)))
				{
					$this->response->setPayload("Success.");
					$this->response->setContentType("text/xml");
					$this->setStatus(true);
					break;
				}
				Error::generateError(60);
			}
			Error::generateError(61);
			break;
		case 'delete':
			break;
	}
?>

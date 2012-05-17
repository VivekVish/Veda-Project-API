<?php

require_once("classes/resources/TempQuestion.php");
require_once("classes/resources/User.php");
$questionBlueprint = new TempQuestion();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
		if ($questionBlueprint->loadFromUri($this->request->getUri()))
		{
			$questionBlueprint->buildJSON();
			$this->response->setPayload($questionBlueprint->getJSON());
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
	case 'put':
	case 'post':
        $payload = json_decode($this->request->getPayload());
        $uriArr = explode('/',trim($this->request->getUri()));
		if ($questionBlueprint->loadFromPayload($payload,$uriArr[4]))
		{
			if ($questionBlueprint->save(User::usernameToId($payload->username)))
			{
                $this->response->setPayload("Success.");
				$this->setStatus(true);
				break;
			}
		}
		$this->setStatus(false);
		break;
	case 'delete':
		if ($questionBlueprint->loadFromUri($this->request->getUri()))
		{
			if($questionBlueprint->delete())
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
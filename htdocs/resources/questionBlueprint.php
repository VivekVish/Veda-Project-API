<?php

require_once("classes/resources/QuestionBlueprint.php");
$questionBlueprint = new QuestionBlueprint();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
		if ($questionBlueprint->loadFromUri($this->request->getUri()))
		{
			$questionBlueprint->buildXML();
			$this->response->setPayload($questionBlueprint->getXML());
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
	case 'put':
	case 'post':
		if ($questionBlueprint->loadFromPayload($this->request->getPayload()))
		{
			if ($questionBlueprint->save())
			{
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
				$this->setStatus(true);
				break;
			}
		}
		$this->setStatus(false);
		break;
}
?>
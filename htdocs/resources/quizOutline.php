<?php

require_once("classes/resources/TestBlueprint.php");
require_once("classes/resources/User.php");
$quizBlueprint = new TestBlueprint();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
        if ($quizBlueprint->loadFromUri($this->request->getUri()))
		{
			$quizBlueprint->buildJSON();
			$this->response->setPayload($quizBlueprint->getJSON());
            $this->response->setContentType("text/xml");
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
	case 'put':
	case 'post':
		break;
	case 'delete':
		break;
}
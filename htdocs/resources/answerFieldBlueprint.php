<?php
require_once("classes/resources/AnswerFieldBlueprint.php");
$answerFieldBlueprint = new AnswerFieldBlueprint();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
		if ($answerFieldBlueprint->loadFromUri($this->request->getUri()))
		{
			$answerFieldBlueprint->buildJSON();
			$this->response->setPayload($answerFieldBlueprint->getJSON());
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
	case 'put':
	case 'post':
		if ($answerFieldBlueprint->loadFromPayload($this->request->getPayload()))
		{
			if ($answerFieldBlueprint->save())
			{
				$this->setStatus(true);
				break;
			}
		}
		$this->setStatus(false);
		break;
                //we don't have a delete function yet
                /*
	case 'delete':
		if ($answerFieldBlueprint->loadFromUri($this->request->getUri()))
		{
			if($answerFieldBlueprint->delete())
			{
				$this->setStatus(true);
				break;
			}
		}
		$this->setStatus(false);
		break;
                 
                 */
}
?>

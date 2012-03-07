<?php
require_once("classes/resources/AnswerBank.php");
$answerBank = new AnswerBank();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
		if ($answerBank->loadFromUri($this->request->getUri()))
		{
			$answerBank->buildJSON();
			$this->response->setPayload($answerBank->getJSON());
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
	case 'put':
	case 'post':
		if ($answerBank->loadFromPayload($this->request->getPayload()))
		{
			if ($answerBank->save())
			{
				$this->setStatus(true);
				break;
			}
		}
		$this->setStatus(false);
		break;
              
}
?>

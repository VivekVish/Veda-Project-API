<?php

require_once("classes/resources/QuestionBlueprint.php");
require_once("classes/resources/Question.php");
$questionBlueprint = new QuizBlueprint();
$question = new Question();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
                // @todo $questionBlueprint appears to be of type QuizBlueprint
		$quizBlueprint->loadFromId($id);
		$this->response->setPayload($lesson->getXML());	
		$this->response->setContentType("Text/XML");
		$this->setStatus(true);
		break;
	case 'put':
	case 'post':
		break;
	case 'delete':
		break;
}
?>

<?php

require_once("classes/resources/QuizBlueprint.php");
$quizBlueprint = new QuizBlueprint();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
		$quizBlueprint->loadFromUri($this->request->getURI());
		$quizBlueprint->loadQuestionBlueprintData();
		$quiz = new Quiz($quizBlueprint->getXML());		
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

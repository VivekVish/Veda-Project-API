<?php

require_once("classes/resources/Subject.php");
$subject = new Subject();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
		$subject->loadFromUri($this->request->getUri());
		$subject->loadChildrenIds();
		$subject->buildJSON();
		$this->response->setPayload($subject->getJSON());
		$this->setStatus(true);
		break;
	case 'put':
	case 'post':
		$subject->loadFromPayload($this->request->getPayload());
		if ($subject->save())
		{
			$this->setStatus(true);
		}
		else
		{
			$this->setStatus(false);
		}
		break;
	case 'delete':
		$subject->loadFromUri($this->request->getUri());
		if($subject->delete())
		{
			$this->setStatus(true);
		}
		else
		{
			$this->setStatus(false);
		}
		break;
}
?>

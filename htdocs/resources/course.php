<?php

require_once("classes/resources/Course.php");
$course = new Course();

switch (strtolower($this->request->getMethod()))
{
	case 'get':
		if ($course->loadFromUri($this->request->getUri()))
		{
			$course->buildXML();
			$this->response->setPayload($course->getXML());
			$this->setStatus(true);
			break;
		}
		$this->setStatus(false);
		break;
	case 'put':
	case 'post':
		if ($course->loadFromPayload($this->request->getPayload()))
		{
			if ($course->save())
			{
				$this->setStatus(true);
				break;
			}
		}
		$this->setStatus(false);
		break;
	case 'delete':
		if ($course->loadFromUri($this->request->getUri()))
		{
			if($course->delete())
			{
				$this->setStatus(true);
				break;
			}
		}
		$this->setStatus(false);
		break;
}
?>

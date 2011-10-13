<?php
require_once("classes/resources/Course.php");
require_once("classes/resources/Material.php");
require_once("classes/resources/Image.php");
switch (strtolower($this->request->getMethod()))
{
	case 'get': 
        $courseId = Material::URIToId($this->request->getUri(), "course"); 
		
		$this->response->setPayload(Image::getImagesByCourse($courseId));
		$this->setStatus(true);
		break;
	
	$this->setStatus(false);
	break;
}   
     
?>
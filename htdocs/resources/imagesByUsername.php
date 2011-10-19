<?php
require_once("classes/resources/Image.php");
require_once("classes/resources/User.php");
switch (strtolower($this->request->getMethod()))
{
	case 'get': //only case, probably
		$uriArr = explode("/",trim($this->request->getUri(),"/"));//extract username from second part of URI
                $username = $uriArr[1];
		$userId=User::usernameToId($username);
		$this->response->setPayload(Image::getImagesByUserId($userId));
		$this->setStatus(true);
		break;
	
	$this->setStatus(false);
	break;
}
     
?>

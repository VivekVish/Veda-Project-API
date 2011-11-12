<?php

require_once("classes/resources/Citation.php");

$uri = trim($this->request->getUri(), "/");
$uriArr = explode('/',$uri);

$ids = explode(',',preg_replace('/"/',"",preg_replace('/citation/', "", $uriArr[2])));

switch (strtolower($this->request->getMethod()))
{
	case 'get':
        $response = array();
        foreach($ids as $id)
        {
            $citation = new Citation();
            if($citation->loadFromId($id))
            {
                $response["citation".$id]=$citation->getCitation();
            }
        }
        $this->response->setPayload(json_encode($response));
        $this->setStatus(true);
        break;
	case 'put':
	case 'post':
		break;
	case 'delete':
		break;
}
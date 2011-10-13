<?php

require_once("classes/resources/Ilo.php");

$uri = trim($this->request->getUri(), "/");
$uriArr = explode('/',$uri);

$ids = explode(',',preg_replace('/"/',"",preg_replace('/ilo/', "", $uriArr[2])));

switch (strtolower($this->request->getMethod()))
{
	case 'get':
        $response = array();
        foreach($ids as $id)
        {
            $ilo = new Ilo();
            if($ilo->loadById($id))
            {
                $response["ilo".$id]=$ilo->getContent();
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
?>

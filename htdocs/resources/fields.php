<?php


switch (strtolower($this->request->getMethod()))
{
	case 'get':
		$query = sprintf("SELECT * FROM field WHERE active IS TRUE ORDER BY element_order");
		$result = $GLOBALS['transaction']->query($query,24);
        $jsonArray = array();
        $jsonArray['children'] = array();
        foreach($result as $row)
        {
            $name = str_replace("_", " ", $row['name']);
            array_push($jsonArray['children'],array("name"=>$name,"description"=>$row['description'],"path"=>"/data/material/{$row['name']}/"));
        }
        
        $jsonArray["path"]="/data/material/";
        
        $json = json_encode($jsonArray);
        $this->response->setPayload($json);
        $this->setStatus(true);
		break;
	case 'put':
	case 'post':
        break;
	case 'delete':
        break;
}
?>

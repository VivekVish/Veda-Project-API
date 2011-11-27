<?php


switch (strtolower($this->request->getMethod()))
{
	case 'get':
		$query = sprintf("SELECT * FROM field WHERE active IS TRUE ORDER BY element_order");
		$result = $GLOBALS['transaction']->query($query,24);
        $xml = "<data><schemaVersion>1.0</schemaVersion><timestamp>".date("c")."</timestamp>";
        $xml .= "<fields>";
        foreach($result as $row)
        {
            $name = str_replace("_", " ", $row['name']);
            $xml .= "<field><name>$name</name><description>{$row['description']}</description><path>/data/material/{$row['name']}</path></field>"; 
        }
        $xml .= "</fields>";
        $xml .= "</data>";
        $this->response->setPayload($xml);
        $this->response->setContentType("Text/XML");
        $this->setStatus(true);
		break;
	case 'put':
	case 'post':
	case 'delete':
}
?>

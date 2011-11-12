<?php

if (strtolower($this->request->getMethod()) == 'get')
{
	$query = "SELECT nextval('citations_id_seq')";
	$result = $GLOBALS['transaction']->query($query,109);
    
    $nextval = $result[0]['nextval'];
    
    if (!empty($nextval))
    {
        $this->response->setPayload($nextval);
        $this->setStatus(true);
    }
}
else
{
	$this->setStatus(false);
}
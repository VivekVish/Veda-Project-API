<?php

if (strtolower($this->request->getMethod()) == 'get')
{
	$db = &$GLOBALS['db'];
	$query = "SELECT nextval('ilo_seq')";
	$result = $db->query($query);
	if ($result)
	{
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$nextval = $row['nextval'];
		if (!empty($nextval))
		{
			$this->response->setPayload($nextval);
			$this->setStatus(true);
		}
	}
}
else
{
	$this->setStatus(false);
}

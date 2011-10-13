<?php

require_once("classes/resources/Lesson.php");
require_once("classes/resources/Ilo.php");
$lesson = new Lesson();

switch (strtolower($this->request->getMethod()))
{
	# GET
	case 'get':
		$lesson->loadFromUri($this->request->getURI());
		if ($lesson->loadIlos())
		{
			$ilos = $lesson->getILOs();
			if (count($ilos))
			{
				foreach ($ilos as $ilo)
				{
					$iloContent['ilo'.$ilo->getId()] = $ilo->getContent();
				}
				$iloContent = json_encode($iloContent);
				$this->response->setPayload($iloContent);
				$this->response->setContentType("application/json");
				$this->setStatus(true);
			}
			break;
		}
	# PUT
	case 'put':
        // @todo add logging for errors
		# Get Payload
		$payload = $this->request->getPayload();
		$payload = json_decode($payload);
		
		# Convert from Object to Array
		foreach ($payload as $ndx => $ilo)
		{
			$tmp[$ndx] = $ilo;
		}

		# Load up lesson
		if ($lesson->loadFromUri($this->request->getURI()))
		{
			# Save ILO's
			if ($lesson->setILOs($tmp))
			{
                $lesson->saveIlos();
                $this->response->setContentType("application/json");
                $this->setStatus(true);
                break;
			}
		}
        
        Error::generateError(30,"Lesson: {$lesson->getName()} and URI: {$this->request->getURI()}");
		$this->setStatus(false);
		break;

}
?>

<?php

Class Response
{
	## Member Variables ##

	private $allHeaders = null;
	private $contentType = null;
	private $payload = null;

	## Member Functions ##

	# Constructor
	public function __construct()
	{
	}

	########################################################
    #### Getters and Setters  ##############################
    ########################################################

	# Payload
	public function getPayload()
	{
		return $this->payload;
	}

	# Content Type
	public function getContentType()
	{
		return $this->contentType;
	}

	# All Headers
	public function getHeaders()
	{
		return $this->allHeaders;
	}

	# Payload
	public function setPayload($payload)
	{
		$this->payload = $payload;
	}

	# Content Type
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
	}
}
?>
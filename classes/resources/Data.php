<?php

Class Data 
{
	## Member Variables ##
	private $db = null;

	## Member Functions ##

	# Constructor
	public function __construct($action, $payload, $path)
	{
		# Get DB Handle
		$this->db = $GLOBALS['db'];
		$this->action = $action;
		$this->payload = $payload;
		$this->path = $path;
	}
	
	# Getters
	public function getAction()
	{
		return $this->action;
	}

	public function getPayload()
	{
		return $this->payload;
	}

	public function getPath()
	{
		return $this->path;
	}

	########################################################
    #### Parent functions used by child classes ############
    ########################################################
}

?>

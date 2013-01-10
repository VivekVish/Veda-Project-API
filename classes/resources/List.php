<?php

Class Course
{
	########################################################
	#### Member Variables ##################################
	########################################################

	private $id = null;
	private $subjectId = null;
	private $name = null;

	########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct($action, $payload, $uri)
	{
		# Get DB handle
		$this->db = $GLOBALS['db'];
	}

	# Process main function generically named for all resource handlers
	public function process()
	{
		$action = $this->getAction();
		switch (strtolower($action))
		{
			case 'get':
			case 'put':
			case 'post':
			case 'delete':
		}	
	}

	########################################################
	#### Helper functions for loading object ###############
	########################################################


	########################################################
	#### Database interface functions ######################
	########################################################

	# Save course (creates one if no id is set)
	public function save()
	{

	} 

	# Removes course from database
	public function delete()
	{
	}


	########################################################
	### Getters and Setters ################################
	########################################################

	public function setName($name)
	{
		$this->name = name;
	}

	public function getName()
	{
		return $this->name;
	}
}

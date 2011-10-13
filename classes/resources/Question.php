<?php

Class Question 
{
	##################################################
	##### Member Variables ###########################
	##################################################

	private $db = null;
	private $id = null;
	private $typeId = null;
	private $blueprint = null;
	private $name = null;
	private $schema = null;
	private $content = null;
	private $xml = null;
	private $parameters = null;
	private $arguments = null;
	

	##################################################
	##### Member Functions ###########################
	##################################################

	# Constructor
	public function __construct($blueprint = null)
	{
		$this->db = &$GLOBALS['db'];
		if (!empty($blueprint))
		{
			try
			{
				$blueprintObj = new SimpleXMLElement($blueprint);
				$this->id = (int)$blueprintObj->id;
				$this->type = (string)$blueprintObj->type;
				$this->schema = (string)$blueprintObj->schema;
				foreach ($blueprintObj->parameters as $parameter)
				{
					$this->parameters[] = array("identifier" => (string)$argument->identifier, "type" => (string)$argument->type, "domain" => (string)$argument->domain);
				}
			}
			catch (Exception $e)
			{
				// Error handling
			}
		}
	}

	# Generate arguments
	public function generateArguments()
	{
		if (!empty($this->parameters))
		{
			foreach ($this->parameters as $parameter)
			{
				$domainPair = explode(",", $parameter['domain']);
				$floor = $domainPair[0];
				$ceiling = $domainPair[1];
				if ($parameter['type'] == 'integer')
				{
					$value = rand($floor, $ceiling);
					//$this->arguments[] == array("identifier" => $parameter['identifier'], "value" => $value);	
					$this->arguments[$parameter['identifier']] == $value;	
				}
			}
		}
		else
		{
			$pattern = '/`([A-Za-z])`/';
			foreach ($this->parameters as $parameters)
			{
			}
		}
	}

	# Generate answer
	public function generateAnswer()
	{
	}

	# Generate content
	public function buildQuestion()
	{
		$question = $this->schema;
		foreach($this->parameters as $parameter)
		{
			str_replace("`{$parameter['identifier']}`", $this->arguments[$parameter['identifier']], $question);
		}
		$this->question = $question;
	}

	# Save 
	public function save()
	{
		# New Question 
		if (empty($this->id))
		{
			$query = sprintf();
		}
		# Updating an existing question 
		elseif (!empty($this->lessonId) && !empty($this->userId) && !empty($this->name))
		{
			$query = sprintf();
		}
		
		$result = $this->db->exec($query);
		if ($result)
		{
			return true;
		}
		return false;
	}

	##################################################
	##### Getters and Setters ########################
	##################################################

	# Get id
	public function getId()
	{
		return $this->id;
	}

	# Get name
	public function getName()
	{
		return $this->name;
	}

	# Get lesson id
	public function getLessonId()
	{
		return $this->lessonId;
	}

	# Get blueprint
	public function getBlueprint()
	{
		return $this->blueprint;
	}

	# Get user id
	public function getUserId()
	{
		return $this->userId;
	}

	# Get attempt
	public function getAttempt()
	{
		return $this->attempt;
	}

	# Get questions
	public function getQuestions()
	{
		return $this->questions;
	}

	# Get XML
	public function getXML()
	{
		return $this->xml;
	}

	# Set id
	public function setId($id)
	{
		$this->id = $id;
	}

	# Set name
	public function setName($name)
	{
		$this->name = $name;
	}

	# Set lesson id
	public function setLessonId($lessonId)
	{
		$this->lessonId = $lessonId;
	}

	# Set blueprint
	public function setBlueprint($blueprint)
	{
		$this->blueprint = $blueprint;
	}

	# Set user id
	public function setUserId($userId)
	{
		$this->userId = $userId;
	}

	# Set attempt
	public function setAttempt($attempt)
	{
		$this->attempt = $attempt;
	}

	# Set questions
	public function setQuestions($questions)
	{
		$this->questions = $questions;
	}
}

?>

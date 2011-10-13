<?php

Class Quiz
{
	##################################################
	##### Member Variables ###########################
	##################################################

	private $db = null;
	private $id = null;
	private $blueprint = null;
	private $userId = null;
	private $attempt = null;
	private $lessonId = null;
	private $name = null;
	private $questions = null;
	private $xml = null;
	

	##################################################
	##### Member Functions ###########################
	##################################################

	# Constructor
	public function __construct($blueprint = null)
	{
		if (!empty($blueprint))
		{
			$this->blueprint = $blueprint;
			try 
			{
				$blueprintObj = new SimpleXMLElement($blueprint);
				$this->id = (int)$blueprintObj->id;
				$this->lessonId = (int)$blueprintObj->lessonId;
				$this->name = (string)$blueprintObj->name;
				foreach ($blueprintObj->questionBlueprints as $questionBlueprint)
				{
					$this->questions[] = new Question($questionBlueprint);
				}
			}
			catch (Exception $e)
			{
				// Error handling
			}
		}
	}

	# Save 
	public function save()
	{
		# New quiz
		if (empty($this->id))
		{
			$query = sprintf("INSERT INTO quiz (lesson_id, user_id, name) VALUES (%s, %s, '%s')", pg_escape_string($this->lessonId), pg_escape_string($this->userId), pg_escape_string($this->name));
		}
		# Updating an existing quiz
		elseif (!empty($this->lessonId) && !empty($this->userId) && !empty($this->name))
		{
			$query = sprintf("UPDATE quiz SET lesson_id = %s, user_id = %s, name = %s", pg_escape_string($this->lessonId), pg_escape_string($this->userId), pg_escape_string($this->name));
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

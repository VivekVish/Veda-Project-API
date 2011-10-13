<?php

Class QuestionBlueprint
{
	##################################################
	##### Member Variables ###########################
	##################################################

	private $db = null;
	private $id = null;
	private $blueprint = null;
	private $quizBlueprintId  = null;
	private $type_id = null;
	

	##################################################
	##### Member Functions ###########################
	##################################################

	# Constructor
	public function __construct($blueprint = null)
	{
		$db = &$GLOBALS['db'];
		if (!empty($blueprint))
		{
			try 
			{
				$blueprintObj = new SimpleXMLElement($blueprint);
				$this->id = (int)$blueprintObj->id;
				$this->type = (int)$blueprintObj->type;
				$this->schema = (string)$blueprintObj->schema;
				foreach ($blueprintObj->arguments as $argument)
				{
					$this->arguments[] = array("identifier" => (string)$argument->identifier, "type" => (int)$argument->type, "domain" => (string)$argument->domain);
				}
			}
			catch (Exception $e)
			{
				// Error handling
			}
		}
	}

	# Validate the question definition
	public function validate()
	{
	}

	# Save (always creates a new instance even if it is only being modified)
	public function save()
	{
		$query = sprintf("INSERT INTO question_blueprint (quiz_blueprint_id, type_id, content) VALUES (%s, %s, '%s')", pg_escape_string($this->quizBlueprintId), pg_escape_string($this->typeId), pg_escape_string($this->blueprint));
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
	
	# Get quiz blueprint id
	public function getQuizBlueprintId()
	{
		return $this->quizBlueprintId;
	}

	# Get type id
	public function getTypeId()
	{
		return $this->typeId;
	}

	# Get blueprint 
	public function getBlueprint()
	{
		return $this->blueprint;
	}

	# Set id
	public function setId($id)
	{
		$this->id = $id;
	}

	# Set Type ID
	public function setTypeId($typeId)
	{
		$this->typeId = $typeId;
	}

	# Set Quiz Blueprint Id
	public function setQuizBlueprintId($quizBlueprintId)
	{
		$this->quizBlueprintId = $quizBlueprintId;
	}

	# Set Blueprint
	public function setBlueprint($blueprint)
	{
		$this->blueprint = $blueprint;
	}
}

?>

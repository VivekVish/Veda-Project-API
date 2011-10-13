<?php

Class QuizBlueprint
{
	##################################################
	##### Member Variables ###########################
	##################################################

	private $db = null;
	private $id = null;
	private $blueprint = null;
	private $lessonId = null;
	private $questionBlueprintData = null;
	private $questionBlueprintIds = null;
	private $questionBlueprints = null;
	

	##################################################
	##### Member Functions ###########################
	##################################################

	##################################################
	##### Constructor and Loading helpers ############
	##################################################
	public function __construct($blueprint = null)
	{
		$db = &$GLOBALS['db'];
		if (!empty($blueprint))
		{
			$this->blueprint = $blueprint;
			try
			{
				$blueprintObj = new SimpleXMLElement($this->blueprint);
				$this->id = (string)$blueprintObj->id;
				$this->lessonId = (string)$blueprintObj->lessonId;
				foreach ($blueprintObj->questions as $question)
				{
					$this->questionBlueprintIds[] = (int)$question->id;
				}
			}
			catch (Exception $e)
			{
				// Error handling
			}
		}
	}

	# Load by URI (uses lesson in URI to find blueprint)
	public function loadFromURI($uri)
	{
		$uri = trim($uri, "/");
		$uriArr = explode("/", $uri);
		$lessonName = $uriArr[LESSON_INDEX];
		$courseName = $uriArr[COURSE_INDEX];
		$subjectName = $uriArr[SUBJECT_INDEX];
		$fieldName = $uriArr[FIELD_INDEX];
		$query = sprintf("
							SELECT 
									* 
							FROM 
									quiz_blueprint 
							WHERE 
									lesson_id = (SELECT id FROM lesson WHERE name = '%s' AND course_id = (SELECT id FROM course WHERE name = '%s' AND subject_id = (SELECT id FROM subject WHERE name = '%s' AND field_id = (SELECT id FROM field WHERE name= '%s'))))",
									pg_escape_string($lessonName),
									pg_escape_string($courseName),
									pg_escape_string($subjectName),
									pg_escape_string($fieldName));
		$result = $this->db->query($query);
		if ($result)
		{
			$row = $result->fetch(PDO::FETCH_ASSOC);
			if (!empty($row))
			{
				$this->id = $row['id'];
				$this->lessonId = $row['lesson_id'];
				$this->blueprint = $row['content'];
				return true;
			}
		}
		return false;
	}

	# Validate the quiz definition
	public function validate()
	{
		// 
	}


	# Load question blueprints	
	public function loadQuestionBlueprints()
	{
		if (!empty($this->questionBlueprintIds))
		{
			$idList = implode(",", $this->questionBlueprintIds);
			$query = sprintf("SELECT content FROM question_blueprint WHERE id IN (%s)", pg_escape_string($idList));
			$result = $this->db->query($query);
			if ($result)
			{
				while ($row = $result->fetch(PDO::FETCH_ASSOC))
				{
					$this->quizBlueprints[] = $row['content'];
				}
				return true;
			}
		}
		return false;
	}

	# Load information about associated question blueprints
	public function loadQuestionBlueprintData()
	{
		if (!empty($this->questionBlueprintIds))
		{
			$idList = implode(",", $this->questionBlueprintIds);
			$where = sprintf("question_blueprint.id IN (%s)", pg_escape_string($idList));
		}
		else
		{
			$where = sprintf("quiz_blueprint_id = %s", pg_escape_string($this->id));
		}
		$query = "
							SELECT 
								question_blueprint.id, 
								question_type.name AS type 
								quiz_blueprint_question_blueprint_recurrence.recurrence AS recurrence
							FROM 
								question_blueprint 
								LEFT JOIN question_type ON (question_blueprint.type_id = question_type.id) 
								LEFT JOIN quiz_blueprint_question_blueprint_recurrence ON (question_blueprint.id = quiz_blueprint_question_blueprint_recurrence.question_blueprint_id)
							WHERE 
								$where";
		$result = $this->db->query($query);
		if ($result)
		{
			while ($row = $result->fetch(PDO::FETCH_ASSOC))
			{
				$this->questionBluePrintData[] = array('id' => $row['id'], 'type' => $row['type'], 'recurrence' => $row['recurrence']);
			}
			return true;
		}
		return false;
	}	

	# Save (always creates a new instance even if it is only being modified
	public function save()
	{
		$query = sprintf("INSERT INTO quiz_blueprint (lesson_id) VALUES (%s)", pg_escape_string($this->lessonId));
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

	# Get question blueprint data
	public function getQuestionBlueprintData()
	{
		return $this->questionBlueprintData;
	}

	# Set id
	public function setId($id)
	{
		$this->id = $id;
	}

	# Set lesson ID
	public function setLessonId($lessonId)
	{
		$this->id = $lessonId;
	}

	# Set blueprint
	public function setBlueprint($blueprint)
	{
		$this->blueprint = $blueprint;
	}
}

?>

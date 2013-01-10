<?php

require_once("classes/resources/Material.php");

Class Course extends Material
{
	########################################################
	#### Member Variables ##################################
	########################################################

	########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct()
	{

	}

	########################################################
	#### Helper functions for loading object ###############
	########################################################

	# Load by URI
	public function loadFromUri($uri)
	{
		if (!empty($uri))
		{
            $this->path = $uri;
			$this->id = parent::URIToId($uri,"course");
			
            $this->loadFromId($this->id);
            
            return true;
		}
        
        return false;
	}

	# Load from Payload
	public function loadFromPayload($payload)
	{
		if (!empty($payload))
		{
			try
			{
				$payloadObj = new SimpleXMLElement($payload);
				if ((int)$payloadObj->id)
				{
					$this->id = (int)$payloadObj->id;
				}
				$this->parentId = (int)$payloadObj->parentId;
				$this->name = (string)$payloadObj->name;
				$this->description = (string)$payloadObj->description;
				$this->active = ((string)$payloadObj->active == "true") ? true : false;
				$query = sprintf("SELECT name FROM subject WHERE id = %s", pg_escape_string($this->parentId));
				$result = $GLOBALS['transaction']->query($query,11);
                $this->subjectName = $result[0]['name'];
				
				return true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		return false;
	}
    
    public function loadFromId($id)
    {
        $query = sprintf("SELECT course.*, subject.name AS subject_name FROM course LEFT JOIN subject ON (course.subject_id = subject.id) WHERE course.id='%s'  ORDER BY element_order",pg_escape_string($id));
        $result = $GLOBALS['transaction']->query($query,12);

        $row = $result[0];
        $this->id = $row['id'];
        $this->parentId = $row['subject_id'];
        $this->subjectName = $row['subject_name'];
        $this->name = str_replace("_", " ", $row['name']);
        $this->description = $row['description'];
        $this->active = $row['active'];
    }

	# Load children Ids
	public function loadChildData()
	{
		if (!empty($this->id))
		{
			$query = sprintf("SELECT section.*, 
									 subject.name AS subject_name,
									 field.name	AS field_name
							FROM 
									section	
									LEFT JOIN course ON (course.id = section.course_id)
									LEFT JOIN subject ON (subject.id = course.subject_id)
									LEFT JOIN field ON (field.id = subject.field_id)
							WHERE 
									section.course_id = %s
                            ORDER BY
                                    section.section_order", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query);
            
            if($result!==false&&$result!=="none")
            {
                foreach($result as $row)
                {
                    $name = str_replace("_", " ", $row['name']);
                    $path = "{$this->path}{$row['name']}/";
                    $this->childData[] = array("id" => $row['id'], 
                                                "name" => $name, 
                                                "description" => $row['description'], 
                                                "path" => $path,
                                                "order" => $row['section_order']);
                }
            }
            return true;
		}
		return false;
	}
	
	########################################################
	#### Client interface functions   ######################
	########################################################
    
    public function buildJSON()
    {
        $this->loadChildData();
        $jsonArray =array("id"=>$this->id,"parentId"=>$this->parentId,"name"=>$this->name,"description"=>$this->description,"path"=>$this->path,"active"=>$this->active,"children"=>$this->childData);
        $this->json = json_encode($jsonArray);
    }
	
	########################################################
	#### Database interface functions ######################
	########################################################

	# Save course (creates one if no id is set)
	public function save()
	{
		if (!empty($this->parentId))
		{
			# Creating a new course	
			if (empty($this->id))
			{
				$query = sprintf("INSERT INTO course (subject_id, name, description, active) VALUES (%s, '%s', '%s', '%s')", pg_escape_string($this->parentId), pg_escape_string($this->name), pg_escape_string($this->description), pg_escape_string($this->active));
			}
			# Updating an existing course
			else
			{
				$query = sprintf("UPDATE course SET subject_id = %s, name = '%s', description = '%s', active = '%s' WHERE id = %s", pg_escape_string($this->parentId), pg_escape_string($this->name), pg_escape_string($this->description), pg_escape_string($this->active), pg_escape_string($this->id));
			}
			$result = $GLOBALS['transaction']->query($query,13);
			
            return true;
		}
		return false;
	} 

	# Removes course from database
	public function delete()
	{
		if (!empty($this->id))
		{
			$query = sprintf("DELETE FROM course WHERE id = %s", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query,14);
			return true;
		}
		return false;
	}

    # Get Deleted Lessons
    public function getDeletedLessons()
    {
        $query = sprintf("SELECT a.name AS name, a.lesson_id AS id FROM lesson_history a LEFT JOIN deleted_lessons
                          ON a.lesson_id = deleted_lessons.lesson_id WHERE 
                          (SELECT COUNT(*) FROM lesson_history WHERE lesson_id=a.lesson_id AND revision_date >= a.revision_date) <= 1
                          AND deleted_lessons.course_id=%s",pg_escape_string($this->id));
        $result = $GLOBALS['transaction']->query($query);
        
        return json_encode($result);
    }

	########################################################
	### Getters and Setters ################################
	########################################################

	
}

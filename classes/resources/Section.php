<?php

require_once("classes/resources/Material.php");

Class Section extends Material
{
	########################################################
	#### Member Variables ##################################
	########################################################

	protected $order = null;

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

	# Load from uri
	public function loadFromUri($uri,$dieOnFail=true)
	{
		if (!empty($uri))
		{
			$this->id = parent::URIToId($uri,"section");
            $this->path = $uri;

			$query = sprintf("SELECT section.*, course.name AS course_name FROM section LEFT JOIN course ON (section.course_id = course.id) WHERE section.id = '%s'",$this->id);
            if($dieOnFail)
            {
                $result = $GLOBALS['transaction']->query($query, 66);
            }
            else
            {
                if(!is_null($this->id))
                {
                    $result = $GLOBALS['transaction']->query($query);
                    if($result==="none"||$result===false)
                    {
                        return false;
                    }
                }
                else
                {
                    return false;
                }
            }
			
            $row = $result[0];
            $this->parentId = $row['course_id'];
            $this->name = str_replace("_", " ", $row['name']);
            $this->description = $row['description'];
            $this->order = $row['section_order'];
            $this->active = $row['active'];
            return true;
		}
		return false;
	}

	# Load by payload
	public function loadFromPayload($payload,$uri)
	{
        $this->path = $uri;
        $uriArr = explode("/",trim($uri,"/"));
        
		if (!empty($payload))
		{
			try
			{
				$this->parentId = parent::URIToId($uri,"course");
                $this->name = urldecode($uriArr[SECTION_INDEX]);
				$this->description = (string)$payload->description;
				$this->active = ((string)$payload->active == "true") ? true : false;

				return true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		return false;
	}

	public function loadChildData()
	{
		if (!empty($this->id))
		{
			$query = sprintf("
								SELECT 
										lesson.id,
										lesson.name,
										lesson.description,
										lesson.lesson_order,
										section.name AS section_name,
										course.name AS course_name,
										subject.name AS subject_name,
										field.name AS field_name
								FROM 
										lesson 
										LEFT JOIN section ON (lesson.section_id = section.id)
										LEFT JOIN course ON (section.course_id = course.id)
										LEFT JOIN subject ON (course.subject_id = subject.id)
										LEFT JOIN field ON (subject.field_id = field.id)
								WHERE 
										lesson.section_id = %s
								ORDER BY
										lesson.lesson_order", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query);

            if($result!="none")
            {
                foreach($result as $row)
                {
                    $path = "/data/material/{$row['field_name']}/{$row['subject_name']}/{$row['course_name']}/{$row['section_name']}/{$row['name']}/content/";
                    $name = str_replace("_", " ", $row['name']);
                    $this->childData[] = array("id" => $row['id'], "name" => $name, "description" => $row['description'], "path" => $path, "lessonOrder" => $row['lesson_order']);
                }
            }
            else
            {
                $this->childData = null;
            }
            return true;
		}
		return false;
	}

	########################################################
	#### User interface functions ##########################
	########################################################

	# Builds XML representation of object
	public function buildXML()
	{
		$this->loadChildData();
		$this->xml = "<section><id>{$this->id}</id><courseid>{$this->parentId}</courseid><name>{$this->name}</name><description>{$this->description}</description><path>{$this->path}</path><order>{$this->order}</order>";
		$this->xml .= ($this->active) ? "<active>true</active>" : "<active>false</active>";
		if (!empty($this->childData))
		{
			$this->xml .= "<lessons>";
			foreach ($this->childData as $child)
			{
				$this->xml .= "<lesson><id>{$child['id']}</id><name>{$child['name']}</name><description>{$child['description']}</description><path>{$child['path']}</path><order>{$child['lessonOrder']}</order></lesson>";
			}
			$this->xml .= "</lessons>";
		}
		$this->xml .= "</section>";
	}


	########################################################
	#### Database interface functions ######################
	########################################################

	# Save subject (creates one if no id is set)
	public function save($userId)
	{
		if (!empty($this->parentId) && !empty($this->name))
		{
			# Creating a new section 
			if (empty($this->id))
			{
				$this->active = (isset($this->active)) ? $this->active : true;
				if(empty($this->order))
				{
					$query = sprintf("SELECT MAX(section_order) FROM section WHERE course_id='%s'",pg_escape_string($this->parentId));
					$result = $GLOBALS['transaction']->query($query,67);

                    if($result=="none")
                    {
                        $this->order = 1;
                    }
                    else
                    {
                        $this->order = $result[0]['max']+1;
                    }
				}	

				$query = sprintf("INSERT INTO section (course_id, name, description, active, section_order, user_id) VALUES (%s, '%s', '%s', '%s', '%s', '%s')", pg_escape_string($this->parentId), pg_escape_string($this->name), pg_escape_string($this->description), pg_escape_string($this->active), pg_escape_string($this->order), pg_escape_string($userId));
			}
			# Updating a section
			else
			{
				$query = sprintf("UPDATE section SET course_id = %s, name = '%s', description = '%s', active = '%s' WHERE id = %s", pg_escape_string($this->parentId), pg_escape_string($this->name), pg_escape_string($this->description), pg_escape_string($this->active), pg_escape_string($this->id), pg_escape_string($userId));
			}

			$result = $GLOBALS['transaction']->query($query, 68);
			
            return true;
		}
		return false;
	} 

	# Removes subject from database
	public function delete()
	{
		if (!empty($this->id))
		{
            $query = sprintf("SELECT id FROM lesson WHERE section_id=%s",pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query);
            
            if($result!=="none")
            {
                Error::generateError(69);
            }
            
			$query = sprintf("DELETE FROM section WHERE id = %s", pg_escape_string($this->id));
			$result = $GLOBALS['transaction']->query($query,70);
			
            return true;
		}
		return false;
	}
    
    public function setPosition($newPath,$newOrder,$oldPath,$userId)
	{
		$newCourseId = parent::URIToId($newPath,"course");		
		$oldCourseId = parent::URIToId($oldPath,"course");
		
		if(strcmp($newPath,$oldPath)==0)
		{
			if($this->order>$newOrder)
			{
				$query = sprintf("UPDATE section SET section_order = section_order+1 WHERE section_order<%s AND section_order>=%s AND course_id='%s'",
								pg_escape_string($this->order),
								pg_escape_string($newOrder),
                                pg_escape_string($newCourseId));
			}
			else
			{
                $query = sprintf("UPDATE section SET section_order = section_order-1 WHERE section_order>%s AND section_order<=%s AND course_id='%s'",
								pg_escape_string($this->order),
								pg_escape_string($newOrder),
                                pg_escape_string($newCourseId));
			}

            $result = $GLOBALS['transaction']->query($query,71);
            
            $query = sprintf("UPDATE section SET section_order = %s WHERE id='%s'",
                        pg_escape_string($newOrder),
                        pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query,71);
		}
		else
		{
			$query = sprintf("UPDATE section SET section_order = section_order-1 WHERE section_order>%s AND course_id='%s'",
                            pg_escape_string($this->order),
                            pg_escape_string($oldCourseId));
            
            $result = $GLOBALS['transaction']->query($query,71);
                        
            $query = sprintf("UPDATE section SET section_order = section_order+1 WHERE section_order>=%s AND course_id='%s'",
								pg_escape_string($newOrder),
                                pg_escape_string($newCourseId));
            $result = $GLOBALS['transaction']->query($query,71);
            
            $query = sprintf("UPDATE section SET section_order = %s, course_id= %s WHERE id='%s'",
                                pg_escape_string($newOrder),
                                pg_escape_string($newCourseId),
                                pg_escape_string($this->id));
            
            $result = $GLOBALS['transaction']->query($query,71);
		}
        
        $query = sprintf("INSERT INTO move_content (old_parent_id, new_parent_id, old_order, new_order, user_id, element_type, move_date) VALUES (%s,%s,%s,%s,%s,'section',CURRENT_TIMESTAMP)",$oldCourseId,$newCourseId,$this->order,$newOrder,$userId);
        $GLOBALS['transaction']->query($query,114);
        
        return true;
	}

    public function rename($name,$userId)
    {
        if($name!="")
        {
            $name = preg_replace('/ /','_',$name);
            
            $query = sprintf("SELECT id FROM section WHERE course_id=%s AND name='%s'", pg_escape_string($this->parentId), pg_escape_string($name));
            $result = $GLOBALS['transaction']->query($query);
            
            if($result!=="none")
            {
                Error::generateError(72);
            }
            
            $query = sprintf("UPDATE section SET name = '%s', user_id = '%s' WHERE id = '%s'",
                                    pg_escape_string($name),
                                    pg_escape_string($userId),
                                    pg_escape_string($this->id));

            $GLOBALS['transaction']->query($query,73);

            $this->name = $name;
            return true;
        }
        
        return false;
    }

	########################################################
	### Getters and Setters ################################
	########################################################

	public function getXML()
	{
		return $this->xml;
	}
}

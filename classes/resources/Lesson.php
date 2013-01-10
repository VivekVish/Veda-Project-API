<?php

require_once("classes/resources/Material.php");
require_once("classes/resources/RevisionHistory.php");
require_once("classes/resources/Ilo.php");
require_once("classes/resources/Content.php");
require_once("classes/resources/Citation.php");

Class Lesson extends Content
{
    
	########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct()
	{
        parent::__construct("lesson");
		# ILO's are not present by default
		$this->ilosIntact = false;
	}


	########################################################
	#### Helper functions for loading object ###############
	########################################################

	# Load from path
	public function loadFromUri($uri,$dieOnFail=true)
	{
		return parent::loadFromUri($uri,$dieOnFail);
	}

	# Load object vars from payload
	public function loadFromPayload($payload,$path)
	{
        $uri= trim($path, "/");
        $uriArr = explode("/", $uri);

        $this->parentId = parent::URIToId($path,"section");
        $this->id = parent::URIToId($path,"lesson");

        $this->name = urldecode($uriArr[LESSON_INDEX]);
        $this->description = (string)$payload->description;
        $this->active = ((string)$payload->active == "true") ? true : false;
        
		return parent::loadFromPayload($payload, $path);
	}
    
    public function loadFromId($id,$dieOnFail=true)
    {
        $query = sprintf("SELECT * FROM lesson WHERE id='%s'",pg_escape_string($id));
            
        if($dieOnFail)
        {
            $result = $GLOBALS['transaction']->query($query,37);
        }
        else
        {
            if(!is_null($id))
            {
                $result = $GLOBALS['transaction']->query($query);
                if($result==="none")
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
        $this->parentId = $row['section_id'];
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->order = $row['lesson_order'];
        $this->content = stripslashes($row['content']);
        $this->active = ($row['active'] == "true") ? true : false;
        
        return true;
    }
    
    public function buildJSON()
    {
        $jsonArray = array("id"=>($this->id),"parentId"=>($this->parentId),"name"=>($this->name),
                           "description"=>($this->description),"content"=>htmlentities($this->content),
                           "active"=>($this->active) ? "true" : "false");
        $this->json = json_encode($jsonArray);
    }
    
	########################################################
	#### Database interface functions ######################
	########################################################
    
	# Save lesson (creates one if no id is set)
	public function save($userId,$notes=null)
	{
            if (!empty($this->parentId) && !empty($this->name) && !empty($this->content)) 
            {
                $newILOIds = Content::getILOIds($this->content);
                $oldILOIds = array();
            
			# Update existing lesson
                if (!empty($this->id))
                {
                    $query = sprintf("SELECT content FROM lesson WHERE id='%s'",pg_escape_string($this->id));
                    $result = $GLOBALS['transaction']->query($query);
                    $oldILOIds = Content::getILOIds($result[0]['content']);
                    $query = sprintf("UPDATE lesson SET section_id = '%s', name = '%s', content = '%s' WHERE id='%s'", 
                                    pg_escape_string($this->parentId),
                                    pg_escape_string($this->name),
                                    pg_escape_string($this->content),
                                    pg_escape_string($this->id));
                }
                # New lesson
                else
                {
                    if(empty($this->order))
                    {
                        $query = sprintf("SELECT MAX(lesson_order) FROM lesson WHERE section_id='%s'",pg_escape_string($this->parentId));
                        $result = $GLOBALS['transaction']->query($query);
                    
                        if($result=="none")
                        {
                            $this->order=1;
                        }
                        else
                        {
                            $this->order=((int)$result[0]['max'])+1;
                        }
                    }
				
                    $query = sprintf("INSERT INTO lesson (section_id, name, description, content, lesson_order) VALUES ('%s', '%s','%s', '%s', '%s')",
                                pg_escape_string($this->parentId),
                                pg_escape_string($this->name),
                                pg_escape_string($this->description),
                                pg_escape_string($this->content),
                                pg_escape_string($this->order));
                }
            
                # Run query
                $GLOBALS['transaction']->query($query,38);
            
                $this->id = parent::URIToId($this->path,"lesson");

                $revisionRow = new RevisionRow("lesson_history","lesson");
                $revisionRow->loadFromData(null,$this->id,$this->name,$this->content,$userId,null);
                $revisionRow->save();

                Content::checkILOsExist($this->ilos,$newILOIds);
                $this->saveIlos($userId,$newILOIds,$oldILOIds);

                $this->saveCitations();
            
                return true;
            }

            # Failure
            return false;
	} 
    
	# Removes lesson from database
	public function delete($user_id=null)
	{
            $deadILOs = Content::getILOIds($this->content);

            foreach($deadILOs as $ilo)
            {
                Ilo::killIlo($ilo);
            }

            # Delete query
            $query = sprintf("INSERT INTO deleted_lessons (lesson_id, section_id, course_id, user_id) VALUES (%s,%s,%s,%s)",
                            pg_escape_string($this->id),
                            pg_escape_string($this->parentId),
                            pg_escape_string(parent::URIToId($this->path,"course")),
                            pg_escape_string((string)$user_id));

            $GLOBALS['transaction']->query($query,39);
            
            $query = sprintf("DELETE FROM lesson WHERE id = %s", pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query,40);

            $query = sprintf("DELETE FROM autosave WHERE element_id = %s AND element_type='lesson'", pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query,102);

            $query = sprintf("SELECT id FROM discussion WHERE element_type='lesson' AND element_id=%s", pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query);

            if($result!="none")
            {
                $query = sprintf("DELETE FROM autosave WHERE element_id=%s AND element_type='discussion'",pg_escape_string($result[0]["id"]));;
                $result = $GLOBALS['transaction']->query($query,103);
            }

            $query = sprintf("UPDATE lesson SET lesson_order = lesson_order-1 WHERE lesson_order>%s AND section_id='%s'",
                                pg_escape_string($this->order),
                                pg_escape_string($this->parentId));

            $result = $GLOBALS['transaction']->query($query,41);
        
            # Success
            if ($result)
            {
                return true;
            }

            # Failure
            return false;
	}

	# Set Position
	public function setPosition($newPath,$newOrder,$oldPath,$userId)
	{
		$newSectionId = parent::URIToId($newPath,"section");
		$oldSectionId = parent::URIToId($oldPath,"section");
        
        $query = sprintf("SELECT id FROM lesson WHERE section_id=%s AND name='%s' AND id!=%s",  pg_escape_string($newSectionId),  pg_escape_string($this->name), pg_escape_string($this->id));
        $result = $GLOBALS['transaction']->query($query);
        
        if($result!='none')
        {
            Error::generateError(100,"Old Path: $oldPath. New Path: $newPath");
        }
		
		if(strcmp($newPath,$oldPath)==0)
		{
			if($this->order>$newOrder)
			{
				$query = sprintf("UPDATE lesson SET lesson_order = lesson_order+1 WHERE lesson_order<%s AND lesson_order>=%s AND section_id='%s'",
								pg_escape_string($this->order),
								pg_escape_string($newOrder),
                                pg_escape_string($newSectionId));
			}
			else
			{
                $query = sprintf("UPDATE lesson SET lesson_order = lesson_order-1 WHERE lesson_order>%s AND lesson_order<=%s AND section_id='%s'",
								pg_escape_string($this->order),
								pg_escape_string($newOrder),
                                pg_escape_string($newSectionId));
			}

            $GLOBALS['transaction']->query($query,42);
            

            $query = sprintf("UPDATE lesson SET lesson_order = %s WHERE id='%s'",
                        pg_escape_string($newOrder),
                        pg_escape_string($this->id));
            $GLOBALS['transaction']->query($query,42);
		}
		else
		{
			$query = sprintf("UPDATE lesson SET lesson_order = lesson_order-1 WHERE lesson_order>%s AND section_id='%s'",
                            pg_escape_string($this->order),
                            pg_escape_string($oldSectionId));
            
            $GLOBALS['transaction']->query($query,42);
            
            $query = sprintf("UPDATE lesson SET lesson_order = lesson_order+1 WHERE lesson_order>=%s AND section_id='%s'",
								pg_escape_string($newOrder),
                                pg_escape_string($newSectionId));
            
            $GLOBALS['transaction']->query($query,42);
            
            $query = sprintf("UPDATE lesson SET lesson_order = %s, section_id= %s WHERE id='%s'",
                                pg_escape_string($newOrder),
                                pg_escape_string($newSectionId),
                                pg_escape_string($this->id));
            
            $GLOBALS['transaction']->query($query,42);
		}
        
        $query = sprintf("INSERT INTO move_content (old_parent_id, new_parent_id, old_order, new_order, user_id, element_id,element_type, move_date) VALUES (%s,%s,%s,%s,%s,%s,'lesson',CURRENT_TIMESTAMP)",$oldSectionId,$newSectionId,$this->order,$newOrder,$userId,$this->id);
        $GLOBALS['transaction']->query($query,113);
        
        return true;
	}
    
    public function rename($name,$userId)
    {
        if($name!="")
        {
            $name = preg_replace('/ /','_',$name);
            
            $query = sprintf("SELECT id FROM lesson WHERE section_id=%s AND name='%s'", pg_escape_string($this->parentId), pg_escape_string($name));
            $result = $GLOBALS['transaction']->query($query);
            
            if($result!=="none")
            {
                Error::generateError(44);
            }
            
            $query = sprintf("UPDATE lesson SET name = '%s' WHERE id = '%s'",
                                    pg_escape_string($name),
                                    pg_escape_string($this->id));

            $GLOBALS['transaction']->query($query,45);

            $this->name = $name;
            
            $revisionRow = new RevisionRow("lesson_history","lesson");
            $revisionRow->loadFromData(null,$this->id,$this->name,$this->content,$userId,null);
            $revisionRow->save();
            
            return true;
        }
        
        return false;
    }
    
    public static function recoverDeletedLessons($uri,$lesson_ids,$userId)
    {
        $course_id = Material::URIToId($uri,"course");
        
        $successful_recoveries = array();
        
        foreach($lesson_ids as $id)
        {
            $query = sprintf("SELECT lesson_id,content,name,user_id FROM lesson_history WHERE lesson_id=%s ORDER BY revision_date DESC",pg_escape_string($id));
            $result = $GLOBALS['transaction']->query($query,46);
            
            $recoveredLessonContents = $result[0];
            
            $query = sprintf("SELECT section_id FROM deleted_lessons WHERE lesson_id=%s",pg_escape_string($id));
            $result = $GLOBALS['transaction']->query($query,47);

            $section_id = $result[0]['section_id'];
            
            $query = sprintf("SELECT course_id FROM section WHERE id = %s",$section_id);
            $result = $GLOBALS['transaction']->query($query);
            
            if($result==="none")
            {
                $query = sprintf("SELECT id,name FROM section WHERE course_id=%s ORDER BY section_order DESC",$course_id);
                $result = $GLOBALS['transaction']->query($query, 48);
                
                $section_id = $result[0]['id'];
                $section_name = $result[0]['name'];
            }
            else
            {
                $query = sprintf("SELECT name FROM section WHERE id=%s",$section_id);
                $result = $GLOBALS['transaction']->query($query, 48);
                
                $section_name = $result[0]['name'];
                
            }
            
            $query = sprintf("SELECT MAX(lesson_order) FROM lesson WHERE section_id='%s'",$section_id);
            
            $result = $GLOBALS['transaction']->query($query);
			
            if($result=="none")
            {
                $order=1;
            }
            else
            {
                $order=((int)$result[0]['max'])+1;
            }
            
            $name = $recoveredLessonContents['name'];
            
            // Ensure lesson of that name doesn't already exist
            $query = sprintf("SELECT id FROM lesson WHERE name='%s' AND section_id=%s",pg_escape_string($name),pg_escape_string($section_id));
            $result = $GLOBALS['transaction']->query($query);
            
            if($result!=="none")
            {
                Error::generateError(49,"Lesson name:$name, Section name: $section_name.");
            }
            
            $query = sprintf("INSERT INTO lesson (id,section_id, name, description, content, lesson_order) VALUES ('%s', '%s','%s', '%s', '%s','%s')",
                                pg_escape_string($id),
                                pg_escape_string($section_id),
                                pg_escape_string($recoveredLessonContents['name']),
                                pg_escape_string(preg_replace('/_/', ' ', $recoveredLessonContents['name'])),
                                pg_escape_string($recoveredLessonContents['content']),
                                pg_escape_string($order));
            
			$GLOBALS['transaction']->query($query,50);
            
            $resurrectILOs = Content::getILOIds($recoveredLessonContents['content']);
        
            foreach($resurrectILOs as $ilo)
            {
                Ilo::resurrectIlo($ilo);
            }
            
            $revisionRow = new RevisionRow("lesson_history","lesson");
            $revisionRow->loadFromData(null,$id,$recoveredLessonContents['name'],$recoveredLessonContents['content'],$userId,null);
            $revisionRow->save();
            
            $query = sprintf("DELETE FROM deleted_lessons WHERE lesson_id=%s",$id);
            $GLOBALS['transaction']->query($query,51);
            
            array_push($successful_recoveries,array("name"=>$recoveredLessonContents['name'],"section_name"=>$section_name,"order"=>$order,"id"=>$id));
        }
        
        return $successful_recoveries;
    }
}

?>
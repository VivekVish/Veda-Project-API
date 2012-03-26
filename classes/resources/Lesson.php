<?php

require_once("classes/resources/Material.php");
require_once("classes/resources/RevisionHistory.php");
require_once("classes/resources/Ilo.php");
require_once("classes/resources/Citation.php");

Class Lesson extends Material
{
	########################################################
	#### Member Variables ##################################
	########################################################

	protected $parentId = null;
	protected $content = null;
	protected $ilos = array();
    protected $citations = array();
	protected $ilosIntact = null;
    protected $order = null;
    protected $json = null;
    protected $notes = null;
    protected $userId = null;
    
	########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct()
	{
		# ILO's are not present by default
		$this->ilosIntact = false;
	}


	########################################################
	#### Helper functions for loading object ###############
	########################################################

	# Load from path
	public function loadFromUri($uri,$dieOnFail=true)
	{
		if (!empty($uri))
		{
            $this->id = parent::URIToId($uri,"lesson");
            $this->path = $uri;
            
            if($this->loadFromId($this->id,$dieOnFail))
            {
                return true;
            }
		}
		return false;
	}

	# Load object vars from payload
	public function loadFromPayload($payload,$path)
	{
		try
		{             
            if(isset($payload->content))
            {
                $content = html_entity_decode($payload->content);
                require_once("includes/htmlpurifier.php");
                $content = $purifier->purify($content);
            }
            else
            {
                $content = "<section></section>";
            }
            
            if(preg_match('/<script/',$content)>0)
            {
                Error::generateError(35,"Content: {$content}.");
            }
            
            $uri= trim($path, "/");
			$uriArr = explode("/", $uri);
            
            $this->parentId = parent::URIToId($path,"section");
            $this->id = parent::URIToId($path,"lesson");
            
			$this->name = urldecode($uriArr[LESSON_INDEX]);
			$this->description = (string)$payload->description;
			$this->content = $content;
			$this->active = ((string)$payload->active == "true") ? true : false;
            $this->userId = User::usernameToId($payload->username);
            $this->path=$path;
			$this->loadILOsFromArray(json_decode($payload->ilos));
            $this->loadCitationsFromArray(json_decode($payload->citations));
			$this->ilosIntact = true;
			return true;
		}
		catch (Exception $e)
		{
            Error::generateError(36, "Content: $content");
			return false;
		}
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
        $jsonarray = array("id"=>($this->id),"parentId"=>($this->parentId),"name"=>($this->name),
                           "description"=>($this->description),"content"=>htmlentities($this->content),
                           "active"=>($this->active) ? "true" : "false");
        $this->json = json_encode($jsonarray);
    }

	# Build xml
	public function buildXML()
	{
		$this->xml = "<lesson><id>{$this->id}</id><parentId>{$this->parentId}</parentId><name>{$this->name}</name><description>{$this->description}</description><content>".htmlentities($this->content)."</content>";
		$this->xml .= ($this->active) ? "<active>true</active>" : "<active>false</active>";
		$this->xml .= "</lesson>";
	}

	########################################################
	#### Database interface functions ######################
	########################################################
    
	# Save lesson (creates one if no id is set)
	public function save($userId,$notes=null)
	{
		if (!empty($this->parentId) && !empty($this->name) && !empty($this->content)) 
		{
            $newILOIds = Lesson::getILOIds($this->content);
            $oldILOIds = array();
            
			# Update existing lesson
			if (!empty($this->id))
			{
                $query = sprintf("SELECT content FROM lesson WHERE id='%s'",pg_escape_string($this->id));
                $result = $GLOBALS['transaction']->query($query);
                $oldILOIds = Lesson::getILOIds($result[0]['content']);
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

            $this->checkILOsExist($this->ilos,$newILOIds);
            $this->saveIlos($userId,$newILOIds,$oldILOIds);
            
            $this->saveCitations();
            
			return true;
		}

		# Failure
		return false;
	} 
    
	# Removes lesson from database
	public function delete($user_id)
	{
        $deadILOs = Lesson::getILOIds($this->content);
        
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

	# Marks lesson inactive
	public function disable()
	{
		$this->active = false;
		$this->save();
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
        
        $query = sprintf("INSERT INTO move_content (old_parent_id, new_parent_id, old_order, new_order, user_id, element_type, move_date) VALUES (%s,%s,%s,%s,%s,'lesson',CURRENT_TIMESTAMP)",$oldSectionId,$newSectionId,$this->order,$newOrder,$userId);
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
            
            $resurrectILOs = Lesson::getILOIds($recoveredLessonContents['content']);
        
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

	########################################################
	#### Functions for working with ILO's ##################
	########################################################
    
    public static function getILOIds($html)
    {
        require_once('includes/html5lib/Parser.php');
        $dom = HTML5_Parser::parse(html_entity_decode(stripslashes("<html>".$html."</html>")));
        $xmlContent = new SimpleXMLElement($dom->saveXml());
        $iloPlaceHolderArray = $xmlContent->xpath("//*[starts-with(@id,'ilo')]");
        $iloIds = array();

        foreach($iloPlaceHolderArray as $placeholder)
        {
            array_push($iloIds, preg_replace("/ilo/","",(string)$placeholder->attributes()->id));
        }

        return $iloIds;
    }

	# Load's array of ILO's from DB or from content
	public function loadIlos()
	{
		$this->ilos = array();
		$contentXML = new SimpleXMLElement("<parent>".$this->content."</parent>");
		$iloArray = $contentXML->xpath('//*[@data-ilotype]');
		foreach($iloArray as $index => $iloElement)
		{
			foreach($iloElement->attributes() as $name=>$value)
			{
				if($name=="id")
				{
					$id = preg_replace('/ilo/',"",$value);
					$this->ilos[$id] = new Ilo($id, null, null);
				}
			}	
		}

		if(!empty($this->ilos))
		{
			return true;
		}

		return false;
	}
    
    # Load ILOs from Array
    public function loadILOsFromArray($ArrayOfILOs)
    {
		if(sizeof($ArrayOfILOs)>0)
		{
        	foreach ($ArrayOfILOs as $ndx => $ilo)
			{
				$tmp[$ndx] = $ilo;
			}
       		return $this->setILOs($tmp);
		}
		
		return;
    }
    
    # Ensures that ILOs in HTML exist as submitted JSON
    public static function checkILOsExist($submittedILOs,$newILOIds)
    {
        $jsonILOIds = array();
        foreach($submittedILOs as $iloId=>$iloContent)
        {
            array_push($jsonILOIds,$iloId);
        }
        
        $missingILOs = array_diff($newILOIds,$jsonILOIds);
        
        foreach($missingILOs as $iloId)
        {
            $ilo = new Ilo();
            if(!$ilo->loadById($iloId))
            {
                Error::generateError(107);
            }
        }
    }
    
    # Save's ilo's to DB
	public function saveIlos($userId,$newILOIds,$oldILOIds)
	{
        $deadILOs = array_diff($oldILOIds,$newILOIds);
        
        foreach($deadILOs as $ilo)
        {
            Ilo::killIlo($ilo);
        }
        
		foreach($this->ilos as $ilo)
		{
			$ilo->save($userId,$newILOIds);
		}
	}
    
    public function setILOs($ilos)
	{
		# Kill old ilos
		unset($this->ilos);

		# Setup pattern for type extraction
		foreach ($ilos as $id => $ilo)
		{
            $type= $ilo->type;
            $id = substr($id, 3);
            $content = json_encode($ilo);
            $this->ilos[$id] = new Ilo($id, $content, $type);
		}
		return true;
	}
    
    ########################################################
	#### Functions for working with Citations ##############
	########################################################
    public function loadCitationsFromArray($ArrayOfCitations)
    {
        if(sizeof($ArrayOfCitations)>0)
		{
        	foreach ($ArrayOfCitations as $ndx => $ilo)
			{
				$tmp[$ndx] = $ilo;
			}
       		return $this->setCitations($tmp);
		}
		
		return;
    }
    
    public function setCitations($citations)
    {
        # Kill old ilos
		unset($this->citations);

		# Setup pattern for type extraction
		foreach ($citations as $id => $citation)
		{
            $id = substr($id, 8);
            $this->citations[$id] = new Citation();
            $payload = array("user_id"=>$this->userId,"course_id"=>Material::URIToId($this->path,"course"),"citation"=>$citation,"id"=>$id);
            $this->citations[$id]->loadFromPayload($payload);
		}
		return true;
    }
    
    public function saveCitations()
    {
        foreach($this->citations as $citation)
		{
			$citation->save();
		}
    }
    
	########################################################
	### Getters and Setters ################################
	########################################################

	# Set content 
	public function setContent($content)
	{
		$this->content = $content;
	}

	# Set course
	public function setSection($parentId)
	{
		$this->parentId = $parentId;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setPath($path)
	{
		$this->path = $path;
	}
        
    public function setLessonOrder($order)
    {
        $this->order = $order;
    }
    
	public function getName()
	{
		return $this->name;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getSection()
	{
		return $this->parentId;
	}

	public function getILOs()
	{
		return $this->ilos;
	}
    
    public function getJSON()
    {
        return $this->json;
    }

	public function getXML()
	{
		return $this->xml;
	}
        
	public function getPath()
	{
		return $this->path;
	}
    
    public function getLessonOrder()
    {
        return $this->order;
    }
}

?>
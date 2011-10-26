<?php

require_once("classes/resources/Material.php");
require_once("classes/resources/RevisionHistory.php");

Class Discussion extends Material
{
	########################################################
	#### Member Variables ##################################
	########################################################

    protected $elementId = null;
    protected $elementType = null;
    protected $name = null;
	protected $content = null;
	protected $ilos = array();
	protected $ilosIntact = null;
    protected $json = null;
    protected $notes = null;
    
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
	public function loadFromUri($uri)
	{
		if (!empty($uri))
		{
            $this->id = parent::URIToId($uri,"discussion");
            $this->path = $uri;

            if($this->loadFromId($this->id))
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
            $content = html_entity_decode($payload->content);
            require_once("includes/htmlpurifier.php");
            $content = $purifier->purify($content);
            
            if(preg_match('/<script/',$content)>0)
            {
                Error::generateError(98,'Content: $content');
            }
            
            $uri= trim($path, "/");
			$uriArr = explode("/", $uri);
            
            if($uriArr[CONTENT_TYPE_INDEX]=="content")
            {
                $this->elementId = parent::URIToId($uri,"lesson");
                $this->elementType = "lesson";
            }
            else if($uriArr[CONTENT_TYPE_INDEX]=="quiz")
            {
                $this->elementId = parent::URIToId($uri,"quiz");
                $this->elementType = "quiz";
            }

            $this->id = parent::URIToId($uri,"discussion");
            
            $this->name = preg_replace('/_/', ' ', urldecode($uriArr[LESSON_INDEX])).($uriArr[CONTENT_TYPE_INDEX] == "quiz" ? "Quiz" : "")." Discussion";
            
			$this->content = $content;
			$this->loadILOsFromArray(json_decode($payload->ilos));
			$this->ilosIntact = true;
			$this->path=$path;
			return true;
		}
		catch (Exception $e)
		{
            Error::generateError(15);
			return false;
		}
	}
    
    public function loadFromId($id)
    {
        if(is_null($id))
        {
            $uriArr = explode("/",trim($this->path,"/"));
            if(!isset($uriArr[CONTENT_TYPE_INDEX])||$uriArr[CONTENT_TYPE_INDEX]=="content")
            {
                $this->elementType = "lesson";
                $this->name=preg_replace('/_/', ' ', urldecode($uriArr[LESSON_INDEX]))." Discussion";
            }
            else
            {
                $this->elementType = "quiz";
                $this->name=preg_replace('/_/', ' ', urldecode($uriArr[LESSON_INDEX]))." Quiz Discussion";
            }

            $this->content = "<section></section>";
        }
        else
        {
            $query = sprintf("SELECT * FROM discussion WHERE id='%s'",$id);
            $result = $GLOBALS['transaction']->query($query,16);
            $row = $result[0];
            $this->elementId = $row['element_id'];
            $this->elementType = $row['element_type'];
            $this->content = stripslashes($row['content']);
            
            if($this->elementType=="lesson")
            {
                $query = sprintf("SELECT name FROM lesson WHERE id='%s'",$this->elementId);
                $result = $GLOBALS['transaction']->query($query);
                $this->name = $result[0]['name'];
            }
            else if($this->elementType=="quiz")
            {
                Error::generateError(17);
            }
            else
            {
               Error::generateError(18,"Element type: {$this->elementType}.");
            }
        }
        
        return true;
    }
    
    public function buildJSON()
    {
        $jsonarray = array("id"=>($this->id),"elementId"=>($this->elementId),"elementType"=>($this->elementType),"name"=>($this->name),
                           "content"=>htmlentities($this->content));
        $this->json = json_encode($jsonarray);
    }

	########################################################
	#### Database interface functions ######################
	########################################################

	# Save discussion (creates one if no id is set)
	public function save($userId,$notes)
	{
        function getILOIds($html)
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
        
        $newILOIds = getILOIds($this->content);
        $oldILOIds = array();

        # Update existing discussion
        if(!empty($this->id))
        {
            $query = sprintf("SELECT content FROM discussion WHERE id='%s'",pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query);
            $oldILOIds = getILOIds($result[0]['content']);
            $query = sprintf("UPDATE discussion SET element_id = '%s', element_type = '%s', content = '%s' WHERE id='%s'", 
                    pg_escape_string($this->elementId),
                    pg_escape_string($this->elementType),
                    pg_escape_string($this->content),
                    pg_escape_string($this->id));
        }
        # New discussion
        else
        {
            $query = sprintf("INSERT INTO discussion (element_id, element_type, content) VALUES ('%s','%s','%s')",
                            pg_escape_string($this->elementId),
                            pg_escape_string($this->elementType),
                            pg_escape_string($this->content));
        }

        # Run query
        $GLOBALS['transaction']->query($query,"Query ".$query." failed in Discussion::save()");

        $this->id = parent::URIToId($this->path,"discussion");
        $revisionRow = new RevisionRow("discussion_history","discussion");
        $revisionRow->loadFromData(null,$this->id,$this->name,$this->content,$userId,null);
        $revisionRow->save();
        
        require_once("classes/resources/Lesson.php");
        Lesson::checkILOsExist($this->ilos,$newILOIds);
        $this->saveIlos($userId,$newILOIds,$oldILOIds);

        return true;

		# Failure
		return false;
	} 
    
	# Removes discussion from database
	public function delete()
	{
		# Delete query
		$query = sprintf("DELETE FROM discussion WHERE id = %s", pg_escape_string($this->id));
		$result = $GLOBALS['transaction']->query($query,92);;

		# Success
		if ($result)
		{
			return true;
		}

		# Failure
		return false;
	}

	# Marks discussion inactive
	public function disable()
	{
		$this->active = false;
		$this->save();
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

	########################################################
	#### Functions for working with ILO's ##################
	########################################################

	# Load's array of ILO's from DB or from content
	public function loadIlos()
	{
		$this->ilos = array();
		$this->content = preg_replace('/&nbsp;/'," ",$this->content);
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

	# Insert's ILO code into object's content variable for display
	public function insertILOCode()
	{
		foreach ($this->ilos as $id => $ilo)
		{
			$pattern = '/(<ilo id="'.$id.'" \/>)/';
			$replacement = $ilo->getContent();
			$this->content = preg_replace($pattern, $replacement, $this->content);
			$this->ilosIntact = true;
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

	public function setPath($path)
	{
		$this->path = $path;
	}
        
    public function setDiscussionOrder($order)
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
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getDiscussionOrder()
    {
        return $this->order;
    }
    
    public function getElementType()
    {
        return $this->elementType;
    }
    
    public function getElementId()
    {
        return $this->elementId;
    }
}

?>

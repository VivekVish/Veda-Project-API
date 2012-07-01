<?php
require_once("classes/resources/Material.php");
require_once("classes/resources/User.php");
require_once("classes/resources/Ilo.php");
require_once("classes/resources/Content.php");
require_once("classes/resources/Lesson.php");
require_once("classes/resources/Discussion.php");
require_once("classes/resources/Course.php");

class ContentAutosave extends Material
{
    protected $elementType = null;
    protected $userId = null;
    protected $content = null;
    protected $ilos = array();
    protected $json = null;
    protected $citations = array();
    
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
	public function loadFromUri($uri,$dieOnFail=false)
	{        
		if (!empty($uri))
		{
			$uri= trim($uri, "/");
			$uriArr = explode("/", $uri);
            
            if(count($uriArr)==10)
            {
                $this->elementType = "lesson";
                $this->userId = User::usernameToId($uriArr[9]);
            }
            else if(count($uriArr)==11)
            {
                $this->elementType = "discussion";
                $this->userId = User::usernameToId($uriArr[10]);
            }
            
            $this->parentId = parent::URIToId($uri,$this->elementType);
            
            $this->deleteOldEntries();
            $this->deleteSavedEntry();
            
            $query = sprintf("SELECT * FROM autosave WHERE element_id='%s' AND element_type='%s' AND user_id='%s'",pg_escape_string($this->parentId),pg_escape_string($this->elementType),pg_escape_string($this->userId));
  
            if($dieOnFail)
            {
                $result = $GLOBALS['transaction']->query($query,1);
            }
            else
            {
                $result = $GLOBALS['transaction']->query($query);
                
                if($result==="none")
                {
                    return false;
                }
            }
            
            $row = $result[0];
            $this->content = stripslashes($row['content']);
            $this->id = $row['id'];
            $this->path = $uri;
            
            if($this->elementType=="lesson")
            {
                $lesson = new Lesson();
                $lesson->loadFromId($this->parentId);
                $this->name = $lesson->name;
            }
            else if($this->elementType=="discussion")
            {
                $discussion = new Discussion();
                $discussion->loadFromId($this->parentId);
                if($discussion->getElementType()=="lesson")
                {
                    $lesson = new Lesson();
                    $lesson->loadFromId($discussion->getElementId());
                    $this->name = $lesson->name;
                }
                else if($discussion->getElementType()=="course")
                {
                    $course = new Course();
                    $course->loadFromId($discussion->getElementId());
                    $this->name = $course->name;
                }
                else if($discussion->getElementType()=="quiz")
                {
                    Error::generateError(2);
                }
            }

            return true;
		}
		return false;
	}
    
    public function loadFromPayload($payload,$path)
    {
        $uri= trim($path, "/");
        $uriArr = explode("/", $uri);

        if(count($uriArr)==10)
        {
            $this->elementType = "lesson";
            $this->userId = User::usernameToId($uriArr[9]);
        }
        else if(count($uriArr)==11)
        {
            $this->elementType = "discussion";
            $this->userId = User::usernameToId($uriArr[10]);
        }
        
        $this->parentId = parent::URIToId($uri,$this->elementType);
        
        $content = html_entity_decode($payload->content);
        require_once("includes/htmlpurifier.php");
        $this->content = $purifier->purify($content);
        $this->path = $path;
        
        $this->loadILOsFromArray(json_decode($payload->ilos));
        $this->loadCitationsFromArray(json_decode($payload->citations));
        
        if(preg_match('/<script/',$this->content)>0)
        {
            Error::generateError(10,"Content: {$this->content}.");
        }
        
        $this->loadILOsFromArray(json_decode($payload->ilos));
        
        return true;
    }
    
    function buildJSON()
    {
        $jsonarray = array("id"=>($this->id),"elementId"=>($this->parentId),"name"=>($this->name),
                           "content"=>htmlentities($this->content));
        $this->json = json_encode($jsonarray);
    }
    
    ########################################################
	#### Database interface functions ######################
	########################################################
    
    public function checkExistence($uri)
    {
        $uriArr = explode("/", trim($uri,"/"));
       
        if(count($uriArr)==11)
        {
            $this->elementType = "lesson";
            $this->userId = User::usernameToId($uriArr[9]);
        }
        else if(count($uriArr)==12)
        {
            $this->elementType = "discussion";
            $this->userId = User::usernameToId($uriArr[10]);
        }

        $this->parentId = parent::URIToId($uri,$this->elementType);
        
        if(is_null($this->parentId))
        {
            return false;
        }
        
        $this->deleteOldEntries();
        $this->deleteSavedEntry();

        $query = sprintf("SELECT revision_date FROM autosave WHERE element_id='%s' AND element_type='%s' AND user_id='%s'",pg_escape_string($this->parentId),pg_escape_string($this->elementType),pg_escape_string($this->userId));

        $result = $GLOBALS['transaction']->query($query);

        if($result==="none")
        {
            return false;
        }
        else
        {
            return $result[0]['revision_date'];
        }
    }
    
    public function save()
	{
        // Check if id already exists
        $query = sprintf("SELECT element_id,content FROM autosave WHERE element_id='%s' AND element_type='%s' AND user_id='%s'",pg_escape_string($this->parentId),pg_escape_string($this->elementType),pg_escape_string($this->userId));
        $result = $GLOBALS['transaction']->query($query);
        
        $ILOIds = Content::getILOIds($this->content);
        
        if($result==="none")
        {
            $query = sprintf("INSERT INTO autosave (element_type, element_id, user_id, content, revision_date) VALUES ('%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)",
                             pg_escape_string($this->elementType),pg_escape_string($this->parentId),pg_escape_string($this->userId),pg_escape_string($this->content));
            $GLOBALS['transaction']->query($query,3);
        }
        else
        {
            $oldILOIds = Content::getILOIds($result[0]['content']);
            $deadILOs = array_diff($oldILOIds,$ILOIds);
            
            foreach($deadILOs as $ilo)
            {
                Ilo::killIlo($ilo);
            }
            
            $query = sprintf("UPDATE autosave SET content='%s', revision_date=CURRENT_TIMESTAMP
                             WHERE element_id='%s' AND element_type='%s' AND user_id='%s'",
                             pg_escape_string($this->content),pg_escape_string($this->parentId), pg_escape_string($this->elementType),pg_escape_string($this->userId));
            $GLOBALS['transaction']->query($query,4);
        }
        
        foreach($this->ilos as $ilo)
		{
			$ilo->save($this->userId,$ILOIds);
		}
        
        Content::checkILOsExist($this->ilos,$ILOIds);
        $this->deleteOldEntries();
        
        $this->saveCitations();
        
        return true;
    }
    
    # Delete old entries
    public function deleteOldEntries()
    {
        function deleteILOsFromAll($elementArray)
        {
            foreach($elementArray as $element)
            {
                $autosavedILOIds = Content::getILOIds($element['content']);
                if($element['element_type']=='lesson')
                {
                    $lesson = new Lesson();
                    $lesson->loadFromId($element['element_id']);
                    $currentILOIds = Content::getILOIds($lesson->getContent());
                }
                else if($element['element_type']=='discussion')
                {
                    $discussion = new Discussion();
                    $discussion->loadFromId($element['element_id']);
                    $currentILOIds = Content::getILOIds($discussion->getContent());
                }
                else
                {
                    Error::generateError(5);
                }
                
                $deadILOs = array_diff($autosavedILOIds,$currentILOIds);
                
                foreach($deadILOs as $ilo)
                {
                    Ilo::killIlo($ilo);
                }
            }
        }
        
        $query = sprintf("SELECT content,element_id, element_type FROM autosave WHERE revision_date<(SELECT NOW() - '1 day'::INTERVAL)");
        $result = $GLOBALS['transaction']->query($query);
        
        if($result!=="none")
        {
            deleteILOsFromAll($result);
        }
        
        $query = sprintf("DELETE FROM autosave WHERE revision_date<(SELECT NOW() - '1 day'::INTERVAL)");
        $GLOBALS['transaction']->query($query,6);
        
        $query = sprintf("SELECT content,element_id, element_type from autosave WHERE user_id='%s' ORDER BY revision_date DESC OFFSET 5",$this->userId);
        $result = $GLOBALS['transaction']->query($query);
        
        if($result!=="none")
        {
            deleteILOsFromAll($result);
        }
        
        $query = sprintf("DELETE FROM autosave WHERE id in (SELECT id from autosave WHERE user_id='%s' ORDER BY revision_date DESC OFFSET 5)",$this->userId);
        $GLOBALS['transaction']->query($query,7);
        
        return true;
    }
    
    public function deleteSavedEntry()
    {
        if($this->elementType=="lesson")
        {
            $query = sprintf("SELECT revision_date from lesson_history where lesson_id='%s' and user_id='%s' ORDER BY revision_date DESC LIMIT 1",pg_escape_string($this->parentId),pg_escape_string($this->userId));
            $result = $GLOBALS['transaction']->query($query);
            if($result=="none")
            {
                return;
            }
        }
        else if($this->elementType=="discussion")
        {
            $query = sprintf("SELECT revision_date from discussion_history where discussion_id='%s' and user_id='%s' ORDER BY revision_date DESC LIMIT 1",pg_escape_string($this->parentId),pg_escape_string($this->userId));
            $result = $GLOBALS['transaction']->query($query);
            if($result=="none")
            {
                return;
            }
        }
        
        $elementDate = date("G:i, d F Y",strtotime($result[0]['revision_date']));
        
        $query = sprintf("SELECT revision_date from autosave WHERE element_id='%s' AND user_id='%s' AND element_type='%s'",pg_escape_string($this->parentId),pg_escape_string($this->userId),pg_escape_string($this->elementType));
        $result = $GLOBALS['transaction']->query($query);
        
        if($result!="none")
        {
            $autosaveDate = date("G:i, d F Y",strtotime($result[0]['revision_date']));

            if(strtotime($elementDate)>strtotime($autosaveDate))
            {
                $query = sprintf("DELETE FROM autosave WHERE element_id='%s' AND user_id='%s' AND element_type='%s'",pg_escape_string($this->parentId),pg_escape_string($this->userId),pg_escape_string($this->elementType));
                $GLOBALS['transaction']->query($query,8);
            }
        }
    }
    
    ########################################################
	#### Functions for working with ILO's ##################
	########################################################
    
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
    
    public function getJSON()
    {
        return $this->json;
    }
}
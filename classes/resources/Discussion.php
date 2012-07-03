<?php

require_once("classes/resources/Material.php");
require_once("classes/resources/RevisionHistory.php");
require_once("classes/resources/Ilo.php");
require_once("classes/resources/Content.php");
require_once("classes/resources/Citation.php");

Class Discussion extends Content
{
	########################################################
	#### Member Variables ##################################
	########################################################

    protected $elementId = null;
    protected $elementType = null;
    
	########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct()
	{
		# ILO's are not present by default
        parent::__construct("discussion");
		$this->ilosIntact = false;
	}


	########################################################
	#### Helper functions for loading object ###############
	########################################################

	# Load from path
	public function loadFromUri($uri)
	{
		return parent::loadFromUri($uri,true);
	}

	# Load object vars from payload
	public function loadFromPayload($payload,$path)
	{
        $uri= trim($path, "/");
        $uriArr = explode("/", $uri);

        $this->elementId = parent::URIToId($uri,"lesson");
        $this->elementType = "lesson";
        
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
        
        return parent::loadFromPayload($payload, $path);
	}
    
    public function loadFromId($id,$dieOnFail=true)
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
	public function save($userId,$notes=null)
	{        
        $newILOIds = Content::getILOIds($this->content);
        $oldILOIds = array();

        # Update existing discussion
        if(!empty($this->id))
        {
            $query = sprintf("SELECT content FROM discussion WHERE id='%s'",pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query);
            $oldILOIds = Content::getILOIds($result[0]['content']);
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
        Content::checkILOsExist($this->ilos,$newILOIds);
        $this->saveIlos($userId,$newILOIds,$oldILOIds);
        $this->saveCitations();

        return true;
	} 
    
	# Removes discussion from database
	public function delete($userId=null)
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
}

?>

<?php

require_once("classes/resources/Material.php");

Class RevisionRow
{
    ########################################################
	#### Member Variables ##################################
	########################################################
    private $revisionId = null;
    private $resourceId = null;
    private $name = null;
    private $content = null;
    private $userId = null;
    private $timestamp = null;
    private $notes = null;
    private $resourceTable = null;
    private $resourceType = null;
    
    ########################################################
	#### Constructor and main function #####################
	########################################################
    public function __construct($resourceTable, $resourceType)
    {
        $this->resourceTable = $resourceTable;
        $this->resourceType = $resourceType;
    }
    
    ########################################################
	#### Helper functions for loading object ###############
	########################################################
    public function loadFromUri($uri,$compareTo=null)
    {
        $uri= trim($uri, "/");
        $uriArr = explode("/", $uri);
        $this->revisionId = $uriArr[count($uriArr)-1];
        
        if(count($uriArr)>(RESOURCE_ID+1))
        {
            $this->resourceId = Material::URIToId($uri,"discussion");
        }
        else
        {
            $this->resourceId = Material::URIToId($uri,"lesson");
        }

        $query = sprintf("SELECT * FROM %s WHERE revision_id=%s AND %s_id=%s", 
                         pg_escape_string($this->resourceTable),
                         pg_escape_string($this->revisionId),
                         pg_escape_string($this->resourceType),
                         pg_escape_string($this->resourceId));
        $result = $GLOBALS['transaction']->query($query,63);

        $this->name = $result[0]['name'];
        $this->content = stripslashes($result[0]['content']);
        $this->userId = $result[0]['user_id'];
        $this->timestamp = $result[0]['revision_date'];
        $this->notes = $result[0]['user_notes'];
        
        if(!is_null($compareTo))
        {
            $comparisonRow = new RevisionRow("lesson_history","lesson");
            $comparisonUriArr = explode("/",trim($uri, "/"));
            array_pop($comparisonUriArr);
            array_push($comparisonUriArr,$compareTo);
            $comparisonRow->loadFromUri(join("/",$comparisonUriArr));
        }
        
        return true;
    }
    
    public function loadFromData($revisionId,$resourceId,$name,$content,$userId,$notes)
    {
        $this->revisionId = $revisionId;
        $this->resourceId = $resourceId;
        $this->name = $name;
        $this->content = $content;
        $this->userId = $userId;
        $this->notes = $notes;
        
        return true;
    }
    
    ########################################################
	#### Database interface functions ######################
	########################################################
    public function save()
    {
        if(is_null($this->revisionId))
        {
            // Get maximum revisionId for a given resourceId
            $query = sprintf("SELECT MAX(revision_id) FROM %s WHERE %s_id='%s'",pg_escape_string($this->resourceTable), pg_escape_string($this->resourceType), pg_escape_string($this->resourceId));
            $result = $GLOBALS['transaction']->query($query);
            if($result!==false)
            {					
                if($result=="none")
                {
                    $this->revisionId=1;
                }
                else
                {
                    $this->revisionId=((int)$result[0]['max'])+1;
                }
            }
            else
            {
                Error::generateError(65);
            }
        }
        
        $query = sprintf("INSERT INTO %s (revision_id,%s_id,name,content,user_id,user_notes,revision_date) VALUES (%s,%s,'%s','%s',%s,'%s',CURRENT_TIMESTAMP)",
                         pg_escape_string($this->resourceTable),
                         pg_escape_string($this->resourceType),
                         pg_escape_string($this->revisionId),
                         pg_escape_string($this->resourceId),
                         pg_escape_string($this->name),
                         pg_escape_string($this->content),
                         pg_escape_string($this->userId),
                         pg_escape_string($this->notes));
        $GLOBALS['transaction']->query($query,64);
        
        return true;
    }
    
    ########################################################
	### Getters and Setters ################################
	########################################################
    public function getJSON()
    {
        $json = json_encode(array("revisionId"=>$this->revisionId,"resourceId"=>$this->resourceId,"name"=>$this->name,"content"=>htmlentities($this->content),"userId"=>$this->userId,"notes"=>$this->notes));
        return $json;
    }
}

Class RevisionHistory
{
	########################################################
	#### Member Variables ##################################
	########################################################
    private $resourceRows = null;
    private $resourceTable = null;
    private $resourceType = null;
    private $resourceId = null;
    
    ########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct($resourceTable, $resourceType, $resourceId)
	{
        $this->resourceRows = array();
        $this->resourceTable = $resourceTable;
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;
	}

    ########################################################
	#### Database interface functions ######################
	########################################################
    public function getHistory($startNum = 1, $endNum = 100, $oldToNew = true)
    {
        $query = sprintf("SELECT oldtable.%s_id, oldtable.revision_id, oldtable.username, oldtable.user_notes, oldtable.revision_date, oldtable.name
                          FROM (SELECT * FROM %s LEFT JOIN usernames ON %s.user_id=usernames.id WHERE %s_id=%s ORDER BY revision_id %s) AS oldtable CROSS JOIN
                          (SELECT ARRAY(SELECT revision_id FROM %s WHERE %s_id=%s ORDER BY revision_id %s) AS id)
                          AS oldids CROSS JOIN GENERATE_SERIES(1, (SELECT COUNT(*) FROM %s WHERE %s_id=%s))
                          AS row_number WHERE oldids.id[row_number] = oldtable.revision_id AND row_number>=%s AND row_number<=%s ORDER BY row_number",
                          pg_escape_string($this->resourceType),
                          pg_escape_string($this->resourceTable),
                          pg_escape_string($this->resourceTable),
                          pg_escape_string($this->resourceType),
                          pg_escape_string($this->resourceId),
                          pg_escape_string($oldToNew ? "DESC" : ""),
                          pg_escape_string($this->resourceTable),
                          pg_escape_string($this->resourceType),
                          pg_escape_string($this->resourceId),
                          pg_escape_string($oldToNew ? "DESC" : ""),
                          pg_escape_string($this->resourceTable),
                          pg_escape_string($this->resourceType),
                          pg_escape_string($this->resourceId),
                          pg_escape_string($startNum),
                          pg_escape_string($endNum));
        $result = $GLOBALS['transaction']->query($query);
        
        if($result=="none")
        {
            return array();
        }
        else
        {
            return $result;
        }
    }    
    
	########################################################
	### Getters and Setters ################################
	########################################################
}
?>

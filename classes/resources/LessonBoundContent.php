<?php

require_once("classes/resources/Content.php");

class LessonBoundContent extends Content
{
    protected $lessonId = "";
    protected $name = "";
    
    # Constructor
	public function __construct()
	{
        parent::__construct("additions");
		# ILO's are not present by default
		$this->ilosIntact = false;
	}
    
    public function loadFromPayload($payload,$path)
	{
        $this->name = $payload->name;
        $this->lessonId = Material::URIToId($path,"lesson");
        return parent::loadFromPayload($payload, $path);
    }
    
    public function loadFromId($id,$dieOnFail=true)
    {
        $query = sprintf("SELECT * FROM lesson_additions WHERE id='%s'",pg_escape_string($id));
        
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
        $this->id = $id;
        $this->name = $row['name'];
        $this->content = stripslashes($row['content']);
        $this->lessonId = $row['lesson_id'];
        
        return true;
    }
    
    public function buildJSON()
    {
        $jsonArray = array("id"=>$this->id,"type"=>"content","content"=>$this->content,"name"=>$this->name);
        return json_encode($jsonArray);
    }
    
    ########################################################
	#### Database interface functions ######################
	########################################################

	# Save discussion (creates one if no id is set)
	public function save($userId,$notes=null)
    {
        $newILOIds = Content::getILOIds($this->content);
        $oldILOIds = array();
        
        $query = sprintf("SELECT content FROM lesson_additions WHERE lesson_id=%s and name='%s'",pg_escape_string($this->lessonId),pg_escape_string($this->name));
        $result = $GLOBALS['transaction']->query($query);
        if($result!="none")
        {
            $oldILOIds = Content::getILOIds($result[0]['content']);
            $query = sprintf("UPDATE lesson_additions SET content='%s' WHERE lesson_id='%s' and name='%s'", 
                        pg_escape_string($this->content),
                        pg_escape_string($this->lessonId),
                        pg_escape_string($this->name));
        }
        else
        {
            $query = sprintf("INSERT INTO lesson_additions (type, lesson_id, content, name) VALUES ('%s',%s,'%s','%s')",pg_escape_string("content"),pg_escape_string($this->lessonId),pg_escape_string($this->content),pg_escape_string($this->name));
        }
        
        $GLOBALS['transaction']->query($query,127);
        
        $this->id = parent::URIToId($this->path,"additions");
        
        Content::checkILOsExist($this->ilos,$newILOIds);
        $this->saveIlos($userId,$newILOIds,$oldILOIds);
            
        $this->saveCitations();
            
        return true;
    }
    
    public function delete($userId=null)
    {
        $deadILOs = Content::getILOIds($this->content);
        
        foreach($deadILOs as $ilo)
        {
            Ilo::killIlo($ilo);
        }
        
        $query = sprintf("DELETE FROM lesson_additions WHERE id = %s", pg_escape_string($this->id));
		$result = $GLOBALS['transaction']->query($query,128);
        
        return true;
    }
}

?>

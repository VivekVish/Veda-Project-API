<?php

require_once("classes/resources/Material.php");

class AnswerFieldBlueprint {

    protected $id = null;
    protected $content = null;//will hold answer_field_content
    protected $revision_id = null;
    
    
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
				$this->content = (string)$payloadObj->content;
				
				return true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		return false;
	}
    
    
    public function loadFromUri($uri) {
        $uriArr = explode("/", trim($uri, "/"));
      $id = $uriArr[4]; 
        $this->loadFromID($id);
        return true;
    }

    public function loadFromId($id) {
        $query = sprintf("SELECT * FROM answer_field_blueprint WHERE id = %s", pg_escape_string($id)); 
        $result = $GLOBALS["transaction"]->query($query); 
        $this->id = pg_escape_string($id);//this has to go here because sometimes, the loadFromId function is used externally.
        $this->content = $result["answer_field_content"];
        $this->revision_id = $result["revision_id"];
        
    }
    
    
    public function save(){
        $query = sprintf("SELECT revision_id FROM answer_field_blueprint WHERE id=%s ORDER BY revision_date DESC LIMIT 1", pg_escape_string($this->id)); //get the previous revision ID for this test
        $latest_revision_id = $GLOBALS['transaction']->query($query); //this is a STRING
        $latest_revision_id = intval($latest_revision_id) + 1; //latest_revision_id is now INCREMENTED
        $query = sprintf("INSERT INTO answer_field_blueprint (id, content) VALUES (%s, %s)", pg_escape_string($this->id), pg_escape_string($this->content));
        $GLOBALS['transaction']->query($query);
    }
    
    
    public function buildJSON() {//gets data, stores it as JSON
        $this->JSONData = json_encode(array("id" => $this->id , "revision_id" => $this->revision_id , "content" => $this->content));
    
      }

    public function getJSON() {//returns stored data
        return $this->JSONData;
    }
    
    #################################
    ###########setters/getters#######
    #################################
    
    public function getContent(){
        return $this->content; 
    }
    public function setContent($content){
        $this->content = $content;
    }
    
    
}

?>

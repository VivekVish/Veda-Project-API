<?php


require_once("classes/resources/Material.php");


Class AnswerBank {
     protected $id = null;
     protected $name = null;//something like "elements" or "US presidents"
     protected $user_id = null;//who created bank
     protected $active = null;//"deleted" or not
     protected $course_id = null;
     
     
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
				$this->name = (string)$payloadObj->name;
				$this->user_id = (string)$payloadObj->user_id;
                                $this->active = ((string)$payloadObj->active == "true") ? true : false;
                                $this->course_id = (int)$payloadObj->course_id;
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
        $id = $uriArr[4]; //not sure about this index
        $this->loadFromID($id);
        return true;
    }

    public function loadFromId($id) {
        $query = sprintf("SELECT * FROM answer_field_blueprint WHERE id = %s", pg_escape_string($id)); 
        $result = $GLOBALS["transaction"]->query($query); 
        $this->id = pg_escape_string($id);//this has to go here because sometimes, the loadFromId function is used externally.
        $this->name = $result["name"];
        $this->user_id= $result["user_id"];
        $this->active = $result["active"];
        $this->course_id = $result["course_id"];
    }
     
    
    public function save(){
        $query = sprintf("SELECT revision_id FROM answer_bank WHERE id=%s ORDER BY revision_date DESC LIMIT 1", pg_escape_string($this->id)); //get the previous revision ID for this test
        $latest_revision_id = $GLOBALS['transaction']->query($query); //this is a STRING
        $latest_revision_id = intval($latest_revision_id) + 1; //latest_revision_id is now INCREMENTED
        $query = sprintf("INSERT INTO answer_bank (id, name, user_id, active, course_id) VALUES (%s, %s, %s, %s, %s)", pg_escape_string($this->id), pg_escape_string($this->name),pg_escape_string($this->user_id), pg_escape_string($this->active), pg_escape_string($this->course_id));
        $GLOBALS['transaction']->query($query);
    }
    
    public function buildJSON() {//gets data, stores it as JSON
        $this->JSONData = json_encode(array("id" => $this->id , "name" => $this->name, "user_id"=>$this->user_id, "active"=>$this->active, "course_id"=>$this->course_id));
    
      }

    public function getJSON() {//returns stored data
        return $this->JSONData;
    }
    
     #################################
    ###########setters/getters#######
    #################################
    
     public function getName(){
        return $this->name; 
    }
    public function setName($name){
        $this->name = $name;
    }
    
    
      public function getUserId(){
        return $this->user_id; 
    }
    public function setUserId($user_id){
        $this->user_id = $user_id;
    }
    
      public function getActive(){
        return $this->active; 
    }
    public function setActive($active){
        $this->active = $active;
    }
    public function getCourseId(){
        return $this->course_id;
    }
    public function setCourseId($courseId){
        $this->course_id = $courseId;
    }
        
    
}
?>

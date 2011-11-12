<?php

require_once("classes/resources/Material.php");

class Citation
{

    protected $id = null;
    protected $user_id = null;
    protected $course_id = null;
    protected $citation = null; //string
    protected $active = null; //boolean

    ########################################################
	#### Helper functions for loading object ###############
	########################################################

    public function loadFromPayload($payload) 
    {  //gets image info sent from application when uploaded. Because this function is only used to create a new image, there is no existing URL and it is not an argument
        if (!empty($payload))
        { //confirm payload is not empty
            //$payload[whatever] is set when the application sends the payload
            $this->user_id = $payload['user_id'];
            $this->course_id = $payload['course_id']; //turn URI contained in coursePath into an ID
            $this->active = true;
            $this->citation = $payload['citation'];
            $this->id=$payload['id'];
            return true;
        }

        return false; //if the payload is empty, fail
    }

    public function loadFromUri($uri) 
    { //grab image info from database
        //get image by its ID
        $uriArr = explode("/", trim($uri, "/"));
        $this->id = substr($uriArr[2],8); //data/citations/id
        $this->loadFromId($this->id);

        return true;
    }

    public function loadFromId($id)
    {
        $query = sprintf("SELECT * FROM citations WHERE id = %s", pg_escape_string($id)); //load latest
        $result = $GLOBALS["transaction"]->query($query, 110); //store all the stuff from the row into an array called $results
        $this->courseId = $result[0]["course_id"]; //load stuff from results array into member variable
        $this->user_id = $result[0]["user_id"];
        $this->citation = $result[0]["citation"];
        $this->active = $result[0]["active"];
        $this->id = $id;
        
        return true;
    }
    
    public function buildJSON()
    {
        $jsonarray = array("id"=>($this->id),"citation"=>($this->citation));
        $this->json = json_encode($jsonarray);
    }
    
    ########################################################
	#### Database interface functions ######################
	########################################################
    public function save()
    {
        $query = sprintf("SELECT id FROM citations WHERE id=%s",pg_escape_string($this->id));
        $result = $GLOBALS['transaction']->query($query);
        
        if($result==="none")
        {
            $query = sprintf("INSERT INTO citations (id, user_id, course_id, citation, active) VALUES (%s,%s,%s,'%s','%s')", pg_escape_string($this->id), pg_escape_string($this->user_id),pg_escape_string($this->course_id),pg_escape_string($this->citation),$this->active);
            $GLOBALS['transaction']->query($query,112);
            return true;
        }
        
        return false;
    }
    
    ########################################################
	### Getters and Setters ################################
	########################################################
    public function getJSON()
    {
        return $this->json;
    }
    
    public function getCitation()
    {
        return $this->citation;
    }
}

?>

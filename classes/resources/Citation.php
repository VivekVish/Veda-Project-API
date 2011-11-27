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
    {
        if (!empty($payload))
        {
            $this->user_id = $payload['user_id'];
            $this->course_id = $payload['course_id'];
            $this->active = true;
            $this->citation = $payload['citation'];
            $this->id=$payload['id'];
            return true;
        }

        return false;
    }

    public function loadFromUri($uri) 
    {
        $uriArr = explode("/", trim($uri, "/"));
        $this->id = substr($uriArr[2],8);
        $this->loadFromId($this->id);

        return true;
    }

    public function loadFromId($id)
    {
        $query = sprintf("SELECT * FROM citations WHERE id = %s", pg_escape_string($id));
        $result = $GLOBALS["transaction"]->query($query, 110);
        $this->courseId = $result[0]["course_id"];
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

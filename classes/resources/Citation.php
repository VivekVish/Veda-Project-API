<?php

require_once("classes/resources/Material.php");

class Citation {

    protected $id = null;
    protected $revision_id = null;
    protected $user_id = null;
    protected $course_id = null;
    protected $citation = null; //string
    protected $active = null; //boolean

    ################################
    ######load functions############
    ################################

    public function loadFromPayload($payload) {  //gets image info sent from application when uploaded. Because this function is only used to create a new image, there is no existing URL and it is not an argument
        if (!empty($payload)) { //confirm payload is not empty
            //$payload[whatever] is set when the application sends the payload
            $this->user_id = $payload->user_id;
            $this->course_id = Material::URIToId($payload->coursePath, "course"); //turn URI contained in coursePath into an ID 
            $this->active = $payload->active;
            $this->citation = $payload->citation;
            return true;
        }

        return false; //if the payload is empty, fail
    }

    public function loadFromUri($uri) { //grab image info from database
        //get image by its ID
        $uriArr = explode("/", trim($uri, "/"));
        $this->id = $uriArr[3]; //data/material/citations/id
        if (count($uriArr) == 5) {//if the revision_id is included in the URI
            $this->revision_id = $uriArr[4];
        }
        $this->loadFromId($this->id);

        return true;
    }

    public function loadFromId() {
        if (is_null($this->revision_id)) {//if a revision ID was not specified, load the latest version
            $query = sprintf("SELECT * FROM citation WHERE id = %s ORDER BY revision_date DESC LIMIT 1", pg_escape_string($this->id)); //load latest
            $result = $GLOBALS["transaction"]->query($query, 31); //store all the stuff from the row into an array called $results
            $this->courseId = $result[0]["course_id"]; //load stuff from results array into member variable
            $this->user_id = $result[0]["user_id"];
            $this->citation = $result[0]["citation"];
            $this->active = $result[0]["active"];
            $this->revision_id = $result[0]["revision_id"]; //load revision ID, because it is empty
        } else {//if version specified, load that
            $query = sprintf("SELECT * FROM citation WHERE id = %s AND revision_id = %s", pg_escape_string($this->id), pg_escape_string($this->revision_id)); //load specified
            $result = $GLOBALS["transaction"]->query($query, 31); //store all the stuff from the row into an array called $results
            $this->courseId = $result[0]["course_id"]; //load stuff from results array into member variable
            $this->user_id = $result[0]["user_id"];
            $this->citation = $result[0]["citation"];
            $this->active = $result[0]["active"];
        }
    }

    public function getCitationsByCourse($uri) {
        
        $this->course_id = Material::URIToId($uri, "course");//this converts the URI (made up of human-language strings) into a course_id (which is a number)
        $query = sprintf("SELECT * FROM citation WHERE course_id = %s", pg_escape_string($this->course_id)); //load latest
        $result = $GLOBALS["transaction"]->query($query, 31); //store all the stuff from the row into an array called $results
        /*
        $this->user_id = $result[0]["user_id"];
        $this->citation = $result[0]["citation"];
        $this->active = $result[0]["active"];
        $this->id = $result[0]["id"];
        $this->revision_id = $result[0]["revision_id"];
         */
        return true;
    }
    
    public function getCitationsByUserId($uri) {
        $uriArr = explode("/", trim($uri, "/"));
        $username = $uriArr[1]; ///user/{username}/citations
        $this->user_id = User::usernameToId($username);
        $query = sprintf("SELECT * FROM citation WHERE user_id = %s", pg_escape_string($this->user_id)); //load latest
        $result = $GLOBALS["transaction"]->query($query, 31); //store all the stuff from the row into an array called $results
        /*
        $this->user_id = $result[0]["user_id"];
        $this->citation = $result[0]["citation"];
        $this->active = $result[0]["active"];
        $this->id = $result[0]["id"];
        $this->revision_id = $result[0]["revision_id"];
         */
        return true;
    }

}

?>

<?php

require_once("classes/resources/Material.php");

class AnswerFieldBlueprint {

    protected $id = null;
    protected $content = null;//will hold answer_field_content
    
    public function loadFromUri($uri) {
        $uriArr = explode("/", trim($uri, "/"));
        $this->id = $uriArr[4]; //correct position in URI?
        $this->loadFromID();
        return true;
    }

    public function loadFromId() {
        $query = sprintf("SELECT * FROM answer_field_blueprint WHERE id = %s", pg_escape_string($this->id)); 
        $result = $GLOBALS["transaction"]->query($query); 
        $this->content = $result["answer_field_content"];
        $this->loadChildData();
    }
    
    
    //needs save methods. Should I use revision_ids? 
    
    
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

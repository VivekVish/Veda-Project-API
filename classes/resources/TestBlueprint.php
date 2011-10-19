<?php
require_once("classes/resources/Material.php");

class TestBlueprint
{
    protected $content_type = null;//"lesson" or "course"
    protected $content_id = null;
    protected $id = null;
    protected $number_of_questions = null;
    
    ########################################################
   #### Helper functions for loading object ###############
   ########################################################
  public function loadFromUri($uri) //grab blueprint info from db
   {
           //!!!!!!Make this not die on fail!!!!!!
        //get BP by its ID
        $uriArr=explode("/", trim($uri, "/"));//puts all the stuff like etc/foo/blah into array with {etc, foo, blah}
       $this->id=$uriArr[5];//index in URI for BPs
       $this->loadChildData($this->id);//according to notes, should use loadChildData instead of loadFromId
       return true;
   }
    
    
  public function loadChildData($id)
   {
       $query=sprintf("SELECT * FROM test_blueprint WHERE id = %s", pg_escape_string($id));//get all the stuff from the row where id=the id of the image we want
       $result=$GLOBALS["transaction"]->query($query);//store all the stuff from the row into an array called $results
       
       $this->content_type = $result["content_type"];
        $this->content_id = $reult["content_id"];
        $this->number_of_questions = $result["number_of_questions"];
       
                
   }
   
   public function buildJSON()
   {
   return json_encode(array("content_type"=>$this->content_type, "content_id"=>$this->content_id, "number_of_questions"=>$this->number_of_questions));
   }
   
   public function getJSON($JSONarray)//not sure how this class is supposed to work, this is one way though...
   {
       if(!empty($JSONarray)){
       $this->content_type = $JSONarray->content_type;
       $this->content_id = $JSONarray->content_id;
       $this->number_of_questions = $JSONarray->number_of_questions;
       return true;
       }
       return false;//if JSONarray is empty
   }
   
   
   
} 
?> 
 
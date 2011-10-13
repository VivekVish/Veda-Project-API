<?php

//"Material.php" some useful functions
require_once("classes/resources/Material.php");

class Image
{
    
    //the following is all the information about image stored in db
    
    protected $courseId = null;
    protected $name = null;
    protected $imageType = null;
    protected $id = null; 
   ########################################################
   #### Constructor          ##############################
   ########################################################
    
   public function __construct()
   {
       
       
   }
   
   
   ########################################################
   #### Helper functions for loading object ###############
   ########################################################
   //loading image info from payload
   //need to check if have: image name, course path, user id
   public function loadFromPayload($payload)  //gets image info sent from application when uploaded. Because this function is only used to create a new image, there is no existing URL and it is not an argument
   {
       if (!empty($payload)) //confirm payload is not empty
       {
           //$payload[whatever] is set when the application sends the payload
           $this->name = $payload->imageName;
           $this->courseId = Material::URIToId($payload->coursePath, "course");//turn URI contained in coursePath into an ID 
           $this->imageType = $payload->imageType;
           return true;
       }
       
       return false;//if the payload is empty, fail
   }
   
   public function loadFromUri($uri) //grab image info from database
   {
       //get image by its ID
       $uriArr=explode("/", trim($uri, "/"));//puts all the stuff like images/foo/blah into array with {images, foo, blah}
       $this->id=$uriArr[4];//fourth element in URI is image ID, because this is URI application accesses
       $this->loadFromId($this->id);
       return true;
   }
   
   public function loadFromId($id)
   {
       $query=sprintf("SELECT * FROM images WHERE image_id = %s", pg_escape_string($id));//get all the stuff from the row where id=the id of the image we want
       $result=$GLOBALS["transaction"]->query($query, 31);//store all the stuff from the row into an array called $results
       $this->courseId=$result[0]["course_id"];//load stuff from results array into member variable
       $this->imageType=$result[0]["image_type"];
       $this->name=$result[0]["name"];
       
   }
  
   public static function getImagesByCourse($courseId)
   {
       $query=sprintf("SELECT * FROM images WHERE course_id = %s", pg_escape_string($courseId));//get all stuff with relevant courseId
       $result=$GLOBALS["transaction"]->query($query);
       $images=array();
       for($i=0;$i<sizeOf($result);$i++) {
           $images[i]=array("courseId" => $result[i]["course_id"], "imageType" => $result[i]["image_type"], "name" => $result[i]["name"], "userId" => $result[i]["user_id"], "imageId" => $result[i]["image_id"]);//add all of the relevant tables so $images[1]["name"] is the name of the second image
       }
      
       return json_encode($images);
     
   }
   
   
    
   public static function getImagesByUserId($userId)  
   {
       $query=sprintf("SELECT * FROM images WHERE user_id = %s", pg_escape_string($userId));//get all stuff with relevant userId
       $result=$GLOBALS["transaction"]->query($query);
       $images=array();
       for($i=0;$i<sizeOf($result);$i++) {
           $images[i]=array("courseId" => $result[i]["course_id"], "imageType" => $result[i]["image_type"], "name" => $result[i]["name"], "userId" => $result[i]["user_id"], "imageId" => $result[i]["image_id"]);//add all of the relevant tables so $images[1]["name"] is the name of the second image
       }
      
       return json_encode($images); 
   
   }
  
   public function buildJSON()//package all image info into JSON for sending to application
   {
       return json_encode(array("courseId"=>$this->courseId, "imageType"=>$this->imageType, "name"=>$this->name));
   }
   ########################################################
   #### Database interface functions ###############
   ########################################################
   public function save($userId)
   {
       if(!empty($this->courseId) && !empty($this->name) && !empty($userId))//as long as coursePath, name, &userId are set, good to go
       {
           $query=sprintf("INSERT INTO images (user_id, course_id, image_type, name) VALUES (%s, %s, '%s', '%s')", pg_escape_string($userId), pg_escape_string($this->courseId),  pg_escape_string($this->imageType), pg_escape_string($this->name) );//insert into the images table for the columns u_i, c_i, i_t, name. VALUES tells the values to put in the table. pg_escape_string prevents injection
           $GLOBALS['transaction']->query($query, 96);//run the command contained in $query, and second arg is what to say on failure
          
           $query=sprintf("SELECT id FROM images WHERE course_id = %s AND name = '%s'", pg_escape_string($this->courseId), pg_escape_string($this->name)); //get id for specified image, find image by matching traits with args
           $result = $GLOBALS['transaction']->query($query, 97);//do previous query
           return $result[0]["id"];//zero says first row that you find (only one row, but we need to put anyway) and we want to return "id" element. returns image id
            
       }
       return false;//if failed
   }
   
   public function delete()
   {
       if(!empty($this->id))
       {
            $query = sprintf("DELETE FROM images WHERE id = %s", pg_escape_string($this->id));
            $GLOBALS['transaction']->query($query, 104); 
            return true;//obviously this does not show if the query failed. Does the previous function return a zero on failure? If so, use that for "return".
       }
       return false;
   }
}

?>
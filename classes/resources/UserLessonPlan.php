<?php

require_once("classes/resources/User.php");
require_once("classes/resources/Material.php");
require_once("classes/Error.php");

class UserLessonPlan
{
    ########################################################
    #### Member Variables ##################################
    ########################################################
    protected $id = null;
    protected $userId = null;
    protected $lessonPlanType = null;
    protected $lessonPlanId = null;
    protected $userLessonPlans = array();
    protected $json = null;
    
    ########################################################
    #### Constructor #######################################
    ########################################################
    # Constructor
    public function __construct()
    {
        
    }
    
    ########################################################
    #### Helper functions for loading object ###############
    ########################################################
    # Load from path
    public function loadFromUri($uri)
    {
        $uriArr = explode("/",trim($uri,"/"));
        $this->userId = User::usernameToId($uriArr[2]);
        
        # Load all user lesson plans from this user
        if(count($uriArr)==3)
        {
            $query = sprintf("SELECT * FROM user_lesson_plan WHERE user_id=%s",pg_escape_string($this->userId));
            $result = $GLOBALS['transaction']->query($query);

            if($result!="none")
            {
                foreach($result as $row)
                {
                    if($row["type"]=="standard")
                    {
                        $query = sprintf("SELECT * FROM course WHERE id=%s",pg_escape_string($row["lesson_plan_id"]));
                        $courseResult = $GLOBALS['transaction']->query($query,148);
                        
                        array_push($this->userLessonPlans,array("type"=>$row["type"],"id"=>$row["id"],"courseid"=>$courseResult[0]["id"],"name"=>$courseResult[0]["name"],"description"=>$courseResult[0]["description"],"path"=>Material::IdToURI($courseResult[0]["id"],"course")));
                    }
                    else if($row["type"]=="custom")
                    {
                        $query = sprintf("SELECT * FROM course WHERE id=%s",pg_escape_string($row["lesson_plan_id"]));
                        $lessonPlanResult = $GLOBALS['transaction']->query($query,148);
                        
                        array_push($this->userLessonPlans,array("type"=>$row["type"],"id"=>$row["id"],"lessonplanid"=>$lessonPlanResult[0]['id'],"name"=>$lessonPlanResult[0]['name'],"notes"=>$lessonPlanResult[0]['notes'],"tags"=>$tags,"age"=>$lessonPlanResult[0]['age'],"literacy"=>$lessonPlanResult[0]['literacy'],"gender"=>$lessonPlanResult[0]['gender'],"image"=>$lessonPlanResult[0]['image'],"location"=>$lessonPlanResult[0]['location']));
                    }
                }
            }
        }
        # Load just one user lesson plan
        else if(count($uriArr)==4)
        {
            $query = sprintf("SELECT * FROM user_lesson_plan WHERE user_id=%s AND id=%s",pg_escape_string($this->userId),pg_escape_string($uriArr[3]));
            $result = $GLOBALS['transaction']->query($query,148);
            
            $this->id = $result[0]['id'];
            $this->lessonPlanType = $result[0]['type'];
            $this->lessonPlanId = $result[0]['lesson_plan_id'];
        }
        return true;
    }
    
    # Load from payload
    public function loadFromPayload($payload,$uri)
    {
        $uriArr = explode("/",trim($uri,"/"));
        $this->userId = User::usernameToId($uriArr[2]);
        $lessonPlanPath = $payload->lessonplanpath;
        $lpPathArr = explode("/",trim($lessonPlanPath,"/"));
        
        if($lpPathArr[0]=="data"&&$lpPathArr[1]=="material"&&count($lpPathArr)==5)
        {
            $this->lessonPlanId = Material::URIToId($lessonPlanPath);
            $this->lessonPlanType = "standard";
        }
        else if($lpPathArr[0]=="data"&&$lpPathArr[1]=="lessonplan")
        {
            $this->lessonPlanId = $lpPathArr[2];
            $this->lessonPlanType = "custom";
        }
        return true;
    }
    
    ########################################################
    #### User interface functions ##########################
    ########################################################
    public function buildJSON()
    {
        $this->json = json_encode($this->userLessonPlans);
        
        return true;
    }
    
    ########################################################
    #### Database interface functions ######################
    ########################################################
    public function save()
    {
        $query = sprintf("SELECT * FROM user_lesson_plan WHERE lesson_plan_id=%s AND type='%s' AND user_id=%s",pg_escape_string($this->lessonPlanId),pg_escape_string($this->lessonPlanType),pg_escape_string($this->userId));
        $result = $GLOBALS['transaction']->query($query);
        if($result==="none")
        {
            $query = sprintf("INSERT INTO user_lesson_plan (lesson_plan_id,type,user_id) VALUES (%s,'%s',%s)",pg_escape_string($this->lessonPlanId),pg_escape_string($this->lessonPlanType),pg_escape_string($this->userId));
            $GLOBALS['transaction']->query($query,149);
        }
        else
        {
            Error::generateError(151);
        }
        return true;
    }
    
    public function delete()
    {
        $query = sprintf("DELETE FROM user_lesson_plan WHERE id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,150);
        return true;
    }
    
    ########################################################
    ### Getters and Setters ################################
    ########################################################
    public function getJSON()
    {
        return $this->json;
    }
}

?>

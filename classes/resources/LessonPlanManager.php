<?php

class LessonPlanManager
{
    ########################################################
	#### Member Variables ##################################
	########################################################
    protected $id = null;
    protected $name = null;
    protected $tags = array();
    protected $notes = null;
    protected $json = null;
    protected $childData = array();
    
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
        $uriArr = split("/",trim($uri,"/"));
        $this->id = $uriArr[2];
        $this->loadFromId($this->id);
        
        return true;
    }
    
    # Load from id
    public function loadFromId($id)
    {
        $query = sprintf("SELECT * FROM lesson_plan WHERE id=%s",pg_escape_string($id));
        $result = $GLOBALS['transaction']->query($query,124);
        
        $this->name = $result[0]['name'];
        $this->notes = $result[0]['notes'];
        $this->tags = LessonPlanManager::getTagsByLessonPlanId($id);
        
        return true;
    }
    
    # Load object vars from payload
	public function loadFromPayload($payload)
	{
        foreach($payload->tags as $key=>$value)
        {
            if(trim($value)=="")
            {
                unset($payload->tags[$key]);
            }
            else
            {
                $payload->tags[$key] = trim($value);
            }
        }
        
        $this->id=$payload->id;
        $this->name=$payload->name;
        $this->tags=$payload->tags;
        $this->notes=$payload->notes;
                
        return true;
    }
    
    public function loadChildData()
    {
        $query = sprintf("SELECT lesson_plan_section.id AS id, lesson_plan_section.name AS name, section_order FROM lesson_plan_section LEFT JOIN lesson_plan ON lesson_plan.id=lesson_plan_id WHERE lesson_plan_id=%s ORDER BY section_order",$this->id);
        $result = $GLOBALS['transaction']->query($query);
        
        if($result!="none")
        {
            foreach($result as $row)
            {
                $this->childData[] = array("name"=>preg_replace('/_/',' ',$row['name']),"path"=>"/data/lessonplan/{$this->id}/".$row['name']."/","id"=>$row['id'],"order"=>$row['section_order']);
            }
        }
    }
    
    public function buildJSON()
    {
        $this->loadChildData();
        $this->json=json_encode(array("name"=>$this->name,"tags"=>$this->tags,"notes"=>$this->notes,"id"=>$this->id,"children"=>$this->childData));
        return true;
    }
    
    ########################################################
	#### Helper functions for loading object ###############
	########################################################
    
    public static function getLessonPlansByManager($userId)
    {
        $query = sprintf("SELECT * FROM lesson_plan WHERE user_id=%s",pg_escape_string($userId));
        $result = $GLOBALS['transaction']->query($query);
        
        if($result==="none")
        {
            return array();
        }
        else
        {
            $returnArray = array();
            
            foreach($result as $key=>$row)
            {
                $tags = join(',',LessonPlanManager::getTagsByLessonPlanId($row['id']));
                $returnArray[$key] = array("id"=>$row['id'],"name"=>$row['name'],"notes"=>$row['notes'],"tags"=>$tags);
            }
        }
        
        return $returnArray;
    }
    
    public static function getTagsByLessonPlanId($lessonPlanId)
    {
        $query = sprintf("SELECT * FROM lesson_plan_tag LEFT JOIN lesson_plan_tag_attachment ON tag_id=lesson_plan_tag.id WHERE lesson_plan_id='%s'",pg_escape_string($lessonPlanId));
        $result = $GLOBALS['transaction']->query($query);
        
        $tags = array();
        
        if($result!="none")
        {
            foreach($result as $key=>$row)
            {
                array_push($tags,$row['tag']);
            }
        }
        
        return $tags;
    }
    
    ########################################################
	#### Database interface functions ######################
	########################################################
    
    # Save lesson plan
    public function save($userId)
    {
        if($this->id=="")
        {
            $query = sprintf("SELECT * FROM lesson_plan WHERE user_id=%s AND name='%s'",pg_escape_string($userId),pg_escape_string($this->name));
            $result = $GLOBALS['transaction']->query($query);

            if($result==="none")
            {
                $query = sprintf("INSERT INTO lesson_plan (user_id,name,notes) VALUES (%s,'%s','%s')",pg_escape_string($userId),pg_escape_string($this->name),pg_escape_string($this->notes));
                $GLOBALS['transaction']->query($query,119);
            }
            else
            {
                die("A lesson plan already exists with this name.");
            }

            $query = sprintf("SELECT * FROM lesson_plan WHERE user_id=%s AND name='%s'",pg_escape_string($userId),pg_escape_string($this->name));
            $result = $GLOBALS['transaction']->query($query,119);

            $this->id = $result[0]['id'];
        }
        else
        {
            $query = sprintf("UPDATE lesson_plan SET name='%s',notes='%s' WHERE id=%s",pg_escape_string($this->name),pg_escape_string($this->notes),pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query,119);
            
            $this->removeTagAttachments();
        }
        
        $this->addTags($this->tags);
        
        return true;
    }
    
    # Add tags
    public function addTags($tags)
    {
        foreach($tags as $key => $value)
        {
            $query = sprintf("SELECT * FROM lesson_plan_tag WHERE tag='%s'",$value);
            $result = $GLOBALS['transaction']->query($query);
            
            if($result==="none")
            {
                $query = sprintf("INSERT INTO lesson_plan_tag (tag) VALUES ('%s')",pg_escape_string($value));
                $GLOBALS['transaction']->query($query,120);
            }
            
            $query = sprintf("SELECT * FROM lesson_plan_tag WHERE tag='%s'",pg_escape_string($value));
            $result = $GLOBALS['transaction']->query($query,120);
            
            $tagId = $result[0]['id'];
            
            $query = sprintf("SELECT * FROM lesson_plan_tag_attachment WHERE tag_id=%s AND lesson_plan_id=%s",pg_escape_string($tagId),pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query);
            
            if($result=="none")
            {
                $query = sprintf("INSERT INTO lesson_plan_tag_attachment (tag_id,lesson_plan_id) VALUES (%s,%s)",pg_escape_string($tagId),pg_escape_string($this->id));
                $result = $GLOBALS['transaction']->query($query,120);
            }
        }
        
        return true;
    }
    
    # Delete 
    public function delete()
    {
        $this->removeTagAttachments();
        
        $query = sprintf("DELETE FROM lesson_plan WHERE id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,122);
        
        return true;
    }
    
    # Remove Tag Attachments
    public function removeTagAttachments()
    {
        $query = sprintf("SELECT tag_id FROM lesson_plan_tag_attachment WHERE lesson_plan_id=%s",pg_escape_string($this->id));
        $result = $GLOBALS['transaction']->query($query);
        
        $query = sprintf("DELETE FROM lesson_plan_tag_attachment WHERE lesson_plan_id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,123);
        
        if($result!=="none")
        {
            foreach($result as $key=>$row)
            {
                $query = sprintf("SELECT tag_id FROM lesson_plan_tag_attachment WHERE tag_id=%s",pg_escape_string($row['tag_id']));
                $other = $GLOBALS['transaction']->query($query);
                
                if($other==="none")
                {
                    $query = sprintf("DELETE FROM lesson_plan_tag WHERE id=%s",pg_escape_string($row['tag_id']));
                    $GLOBALS['transaction']->query($query,123);
                }
            }
        }
    }
    
    ########################################################
	#### Section Interactions ##############################
	########################################################
    public function addSection($name)
    {
        if($this->id!="")
        {
            $query = sprintf("SELECT MAX(section_order) FROM lesson_plan_section WHERE lesson_plan_id=%s",$this->id);
            $result = $GLOBALS['transaction']->query($query);
            if($result=="none")
            {
                $order = 1;
            }
            else
            {
                $order = $result[0]['max'];
            }
            
            $query = sprintf("INSERT INTO lesson_plan_section (name,lesson_plan_id,section_order) VALUES ('%s',%s,%s)",pg_escape_string($name),pg_escape_string($this->id),pg_escape_string($order));
            $result = $GLOBALS['transaction']->query($query,133);
            return true;
        }
        
        return false;
    }
    
    ########################################################
	#### Getters and Setters ###############################
	########################################################
    public function getId()
    {
        return $this->id;
    }
    
    public function getJSON()
    {
        return $this->json;
    }
}
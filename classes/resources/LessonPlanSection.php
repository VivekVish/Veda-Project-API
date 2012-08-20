<?php

class LessonPlanSection
{
    ########################################################
	#### Member Variables ##################################
	########################################################
    protected $parentId = null;
    protected $name = null;
    protected $id = null;
    protected $order = null;
    
    ########################################################
	#### Constructor and main function #####################
	########################################################
    # Construtor
    public function __construct()
    {
        
    }
    
    ########################################################
	#### Helper functions for loading object ###############
	########################################################
    # Load from URI
    public function loadFromUri($uri,$dieOnFail=true)
    {
        $uriArr = split("/",trim($uri,"/"));
        $this->parentId = $uriArr[2];

        $query = sprintf("SELECT id FROM lesson_plan_section WHERE lesson_plan_id=%s AND name='%s'",pg_escape_string($this->parentId),pg_escape_string($uriArr[3]));
        if($dieOnFail)
        {
            $result = $GLOBALS['transaction']->query($query,135);
        }
        else
        {
            $result = $GLOBALS['transaction']->query($query);

            if($result=="none")
            {
                return false;
            }
        }
        $this->id = $result[0]['id'];
        
        $this->loadFromId($this->id);
        
        return true;
    }
    
    # Load from Id
    public function loadFromId($id)
    {
        $this->id = $id;
        
        $query = sprintf("SELECT * FROM lesson_plan_section WHERE id=%s ORDER BY section_order",pg_escape_string($id));
        $result = $GLOBALS['transaction']->query($query,134);
        
        $this->name = $result[0]["name"];
        $this->parentId = $result[0]["lesson_plan_id"];
        $this->order = $result[0]["section_order"];
        
        return true;
    }
    
    public function loadFromPayload($payload,$uri)
    {
        $uriArr = split("/",trim($uri,"/"));
        $this->name = urldecode($uriArr[3]);
        $this->parentId = $uriArr[2];
        
        return true;
    }
    
    public function save()
    {
        if(isset($this->id))
        {
            $query = sprintf("UPDATE lesson_plan_section SET name='%s' WHERE id=%s",$this->name,$this->id);
            $GLOBALS['transaction']->query($query,136);
        }
        else
        {
            $query = sprintf("SELECT MAX(section_order) FROM lesson_plan_section WHERE lesson_plan_id=%s",pg_escape_string($this->parentId));
			$result = $GLOBALS['transaction']->query($query);
            
            if($result==="none")
            {
                $this->order = 1;
            }
            else
            {
                $this->order = $result[0]['max']+1;
            }
            
            $query = sprintf("INSERT INTO lesson_plan_section (name,lesson_plan_id,section_order) VALUES ('%s',%s,%s)",$this->name,$this->parentId,$this->order);
            $GLOBALS['transaction']->query($query,136);
        }
        
        return true;
    }
    
    public function delete()
    {
        $query = sprintf("SELECT * FROM lesson_plan_lesson WHERE section_id=%s",pg_escape_string($this->id));
        $result = $GLOBALS['transaction']->query($query);
        
        if($result!=="none")
        {
            Error::generateError(69);
        }
        
        $query = sprintf("DELETE FROM lesson_plan_section WHERE id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,137);
        return true;
    }
    
    public function setPosition($newOrder,$userId)
	{
        if($this->order>$newOrder)
        {
            $query = sprintf("UPDATE lesson_plan_section SET section_order = section_order+1 WHERE section_order<%s AND section_order>=%s AND lesson_plan_id=%s",
                            pg_escape_string($this->order),
                            pg_escape_string($newOrder),
                            pg_escape_string($this->parentId));
        }
        else
        {
            $query = sprintf("UPDATE lesson_plan_section SET section_order = section_order-1 WHERE section_order>%s AND section_order<=%s AND lesson_plan_id='%s'",
                            pg_escape_string($this->order),
                            pg_escape_string($newOrder),
                            pg_escape_string($this->parentId));
        }
        
        $result = $GLOBALS['transaction']->query($query,138);

        $query = sprintf("UPDATE lesson_plan_section SET section_order = %s WHERE id='%s'",
                    pg_escape_string($newOrder),
                    pg_escape_string($this->id));

        $result = $GLOBALS['transaction']->query($query,138);
        
        #If this is going to be used, it needs to be changed. This is taken straight from the Section class.
        #$query = sprintf("INSERT INTO move_content (old_parent_id, new_parent_id, old_order, new_order, user_id, element_id, element_type,  move_date) VALUES (%s,%s,%s,%s,%s,%s,'section',CURRENT_TIMESTAMP)",$oldCourseId,$newCourseId,$this->order,$newOrder,$userId,$this->id);
        #$GLOBALS['transaction']->query($query,138);
        
        return true;
	}
}

?>

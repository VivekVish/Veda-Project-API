<?php

require_once("classes/resources/Material.php");

class LessonPlanLesson
{
    protected $id = null;
    protected $lessonId = null;
    protected $lessonPath = null;
    protected $lessonPlanSectionId = null;
    protected $order = null;
    
    ########################################################
	#### Constructor and main function #####################
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
        $query = sprintf("SELECT lesson_plan_lesson.id FROM lesson_plan_lesson LEFT JOIN lesson_plan_section ON lesson_plan_section.id=lesson_plan_lesson.section_id LEFT JOIN lesson_plan ON lesson_plan.id=lesson_plan_section.lesson_plan_id WHERE lesson_plan_id=%s AND lesson_plan_section.name='%s'",pg_escape_string($uriArr[2]),pg_escape_string($uriArr[3]));
        $result = $GLOBALS['transaction']->query($query,142);
        
        $this->loadFromId($result[0]['id']);
        
        return true;
    }
    
    # Load from Id
    public function loadFromId($id)
    {
        $query = sprintf("SELECT * FROM lesson_plan_lesson WHERE id=%s",pg_escape_string($id));
        $result = $GLOBALS['transaction']->query($query,142);
        
        $this->id=$id;
        $this->lessonPlanSectionId=$result[0]['section_id'];
        $this->order=$result[0]['lesson_order'];
        $this->lessonId=$result[0]['lesson_id'];
        return true;
    }
    
    # Load from payload
    public function loadFromPayload($payload,$uri)
    {
        $uriArr = explode("/",trim($uri,"/"));

        $this->lessonId = Material::URIToId($payload->path,"lesson");
        $this->order = $payload->order;
        
        $query = sprintf("SELECT id from lesson_plan_section WHERE lesson_plan_id=%s and name='%s'", pg_escape_string($uriArr[2]),pg_escape_string($uriArr[3]));
        $result = $GLOBALS['transaction']->query($query,140);
        
        $this->lessonPlanSectionId = $result[0]['id'];
        
        return true;
    }
    
    ########################################################
	#### Database interface functions ######################
	########################################################
    # Saves Lesson
    public function save()
    {
        $query = sprintf("INSERT INTO lesson_plan_lesson (lesson_id, section_id, lesson_order) VALUES (%s, %s, %s)", pg_escape_string($this->lessonId), pg_escape_string($this->lessonPlanSectionId), pg_escape_string($this->order));
        $GLOBALS['transaction']->query($query,139);
        
        $query = sprintf("SELECT id FROM lesson_plan_lesson WHERE lesson_id=%s AND section_id=%s AND lesson_order=%s",pg_escape_string($this->lessonId), pg_escape_string($this->lessonPlanSectionId), pg_escape_string($this->order));
        $result = $GLOBALS['transaction']->query($query,139);
        
        $this->id = $result[0]['id'];
        
        $query = sprintf("SELECT name FROM lesson_additions WHERE lesson_id=%s",pg_escape_string($this->lessonId));
        $result = $GLOBALS['transaction']->query($query);
        
        $query = sprintf("INSERT INTO lesson_plan_lesson_addition (lesson_plan_lesson_id,addition_type) VALUES (%s,'%s')",pg_escape_string($this->id),pg_escape_string("quiz"));
        $GLOBALS['transaction']->query($query,139);

        if($result!=="none")
        {
            foreach($result as $row)
            {
                $query = sprintf("INSERT INTO lesson_plan_lesson_addition (lesson_plan_lesson_id,addition_type) VALUES (%s,'%s')",pg_escape_string($this->id),pg_escape_string($row['name']));
                $GLOBALS['transaction']->query($query,139);
            }
        }
        
        return true;
    }
    
    public function setPosition($newPath,$newOrder,$oldPath)
	{
        $newPathArray = explode("/",trim($newPath,"/"));
        $oldPathArray = explode("/",trim($oldPath,"/"));
		$newLessonPlanId = $newPathArray[2];
		$oldLessonPlanId = $oldPathArray[2];
        
        $query = sprintf("SELECT id FROM lesson_plan_section WHERE lesson_plan_id=%s AND name='%s'",pg_escape_string($newLessonPlanId),pg_escape_string($newPathArray[3]));
        $result = $GLOBALS['transaction']->query($query);
        
        $newSectionId = $result[0]['id'];
        
        $query = sprintf("SELECT id FROM lesson_plan_section WHERE lesson_plan_id=%s AND name='%s'",pg_escape_string($oldLessonPlanId),pg_escape_string($oldPathArray[3]));
        $result = $GLOBALS['transaction']->query($query);
        
        $oldSectionId = $result[0]['id'];
        
        $query = sprintf("SELECT lesson_plan_lesson.id FROM lesson_plan_lesson LEFT JOIN lesson ON lesson_id=lesson.id WHERE lesson_plan_lesson.section_id=%s AND lesson.name='%s' AND lesson_plan_lesson.id!=%s",  pg_escape_string($newSectionId),  pg_escape_string($oldPathArray[4]), pg_escape_string($this->id));
        $result = $GLOBALS['transaction']->query($query);
        
        if($result!='none')
        {
            Error::generateError(100,"Old Path: $oldPath. New Path: $newPath");
        }
		
		if(strcmp($newPath,$oldPath)==0)
		{
			if($this->order>$newOrder)
			{
				$query = sprintf("UPDATE lesson_plan_lesson SET lesson_order = lesson_order+1 WHERE lesson_order<%s AND lesson_order>=%s AND section_id='%s'",
								pg_escape_string($this->order),
								pg_escape_string($newOrder),
                                pg_escape_string($newSectionId));
			}
			else
			{
                $query = sprintf("UPDATE lesson_plan_lesson SET lesson_order = lesson_order-1 WHERE lesson_order>%s AND lesson_order<=%s AND section_id='%s'",
								pg_escape_string($this->order),
								pg_escape_string($newOrder),
                                pg_escape_string($newSectionId));
			}

            $GLOBALS['transaction']->query($query,143);
            

            $query = sprintf("UPDATE lesson_plan_lesson SET lesson_order = %s WHERE id='%s'",
                        pg_escape_string($newOrder),
                        pg_escape_string($this->id));
            $GLOBALS['transaction']->query($query,143);
		}
		else
		{
			$query = sprintf("UPDATE lesson_plan_lesson SET lesson_order = lesson_order-1 WHERE lesson_order>%s AND section_id='%s'",
                            pg_escape_string($this->order),
                            pg_escape_string($oldSectionId));
            
            $GLOBALS['transaction']->query($query,143);
            
            $query = sprintf("UPDATE lesson_plan_lesson SET lesson_order = lesson_order+1 WHERE lesson_order>=%s AND section_id='%s'",
								pg_escape_string($newOrder),
                                pg_escape_string($newSectionId));
            
            $GLOBALS['transaction']->query($query,143);
            
            $query = sprintf("UPDATE lesson_plan_lesson SET lesson_order = %s, section_id= %s WHERE id='%s'",
                                pg_escape_string($newOrder),
                                pg_escape_string($newSectionId),
                                pg_escape_string($this->id));
            
            $GLOBALS['transaction']->query($query,143);
		}

        return true;
	}
    
    public function delete()
    {
        $query = sprintf("DELETE FROM lesson_plan_lesson_addition WHERE lesson_plan_lesson_id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,144);
        
        $query = sprintf("DELETE FROM lesson_plan_lesson WHERE id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,144);
        
        return true;
    }
    
    public function dropAddition($additionType)
    {
        $query = sprintf("DELETE FROM lesson_plan_lesson_addition WHERE lesson_plan_lesson_id=%s AND addition_type='%s'",pg_escape_string($this->id),pg_escape_string($additionType));
        $GLOBALS['transaction']->query($query,145);
        
        return true;
    }
}

?>

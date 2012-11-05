<?php

require_once("classes/resources/Material.php");
require_once("classes/resources/Lesson.php");
require_once("classes/resources/Ilo.php");

class TempQuestion
{
    protected $id=null;
    protected $content=null;
    protected $correctAnswer=null;
    protected $ilos = array();
    protected $name=null;
    protected $questionOrder=null;
    protected $lessonId=null;
    protected $json=null;
    protected $answerChoices=array();
    
    # Constructor
    public function __construct()
    {

    }
    
    public function loadFromUri($uri)
    {
        $uriArr = explode("/", trim($uri, "/"));
        $this->id = $uriArr[3];
        $this->loadFromId($this->id);
        
        return true;
    }
    
    public function loadFromId($id)
    {
        $query = sprintf("SELECT * FROM temp_questions WHERE id = %s", pg_escape_string($id));
        $result = $GLOBALS["transaction"]->query($query);
        
        if($result=="none")
        {
            die("No such question");
        }
        else
        {
            $this->id=$id;
            $this->content=$result[0]['content'];
            $this->correctAnswer=$result[0]['answer'];
            $this->name=$result[0]['name'];
            $this->questionOrder=$result[0]['question_order'];
            $this->lessonId=$result[0]['lesson_id'];
            
            $query = sprintf("SELECT * FROM temp_answers WHERE answerfield_id=%s",pg_escape_string($this->id));
            $result = $GLOBALS['transaction']->query($query);
            
            $this->answerChoices=array();
            
            if($result!=="none")
            {
                for ($i = 0; $i < sizeOf($result); $i++)
                {
                    $choice=$result[$i]['answerchoice'];
                    $order=$result[$i]['answer_order'];
                    $this->answerChoices[$order]=$choice;
                }
            }
        }
        
        return true;
    }
    
    public function loadFromPayload($payload, $id)
    {
        $this->id=$id;
        $this->content=$payload->content;
        $this->correctAnswer=$payload->correctAnswer;
        $this->name=$payload->name;
        $this->questionOrder=$payload->questionOrder;
        $this->lessonId=Material::URIToId(urldecode($payload->lessonPath));
        $this->answerChoices=$payload->answerChoices;
        $this->loadILOsFromArray(json_decode($payload->ilos));
        return true;
    }
    
    public function buildJSON()
    {
        $this->json = json_encode(array("id"=>$this->id,"content"=>$this->content,"correctAnswer"=>$this->correctAnswer,"name"=>$this->name,"questionOrder"=>$this->questionOrder,"lessonId"=>$this->lessonId,"answerChoices"=>$this->answerChoices));
    }
    
    # Load's array of ILO's from DB or from content
	public function loadIlos()
	{
            $this->ilos = array();
            $contentHTML = $this->getHTML($this->content,$answerChoices);
        
            $contentXML = new SimpleXMLElement("<parent>".$contentHTML."</parent>");
            $iloArray = $contentXML->xpath('//*[@data-ilotype]');
            foreach($iloArray as $index => $iloElement)
            {
                foreach($iloElement->attributes() as $name=>$value)
                {
                    if($name=="id")
                    {
                        $id = preg_replace('/ilo/',"",$value);
                        $this->ilos[$id] = new Ilo($id, null, null);
                    }
                }	
            }

            if(!empty($this->ilos))
            {
                return true;
            }

            return false;
	}
    
    # Load ILOs from Array
    public function loadILOsFromArray($ArrayOfILOs)
    {
        if(sizeof($ArrayOfILOs)>0)
        {
            foreach ($ArrayOfILOs as $ndx => $ilo)
            {
                $tmp[$ndx] = $ilo;
            }
            return $this->setILOs($tmp);
        }

        return;
    }
    
    # Save's ilo's to DB
    public function saveIlos($userId,$newILOIds,$oldILOIds)
    {
        $deadILOs = array_diff($oldILOIds,$newILOIds);
        
        foreach($deadILOs as $ilo)
        {
            Ilo::killIlo($ilo);
        }

        foreach($this->ilos as $ilo)
        {
            $ilo->save($userId,$newILOIds);
        }
    }
    
    public function setILOs($ilos)
    {
        # Kill old ilos
        unset($this->ilos);

        # Setup pattern for type extraction
        foreach ($ilos as $id => $ilo)
        {
            $type= $ilo->type;
            $id = substr($id, 3);
            $content = json_encode($ilo);
            $this->ilos[$id] = new Ilo($id, $content, $type);
        }
        
        return true;
    }
    
    public function getHTML($content, $answerChoices)
    {
        $contentHTML = $content;
        foreach($answerChoices as $index=>$answer)
        {
            $contentHTML.=$answer;
        }
        
        return $contentHTML;
    }
    
    public function save($userId)
    {
        $contentHTML = $this->getHTML($this->content,$this->answerChoices);
        
        $newILOIds = Lesson::getILOIds($contentHTML);
        
        if($this->id!="new")
        {
            $oldILOIds = array();
            $oldQuestion = new TempQuestion();
            $oldQuestion->loadFromId($this->id);
            $oldHTML = $this->getHTML($oldQuestion->content,$oldQuestion->answerChoices);
            
            $oldILOIds = Lesson::getILOIds($oldHTML);
            
            if(is_null($this->questionOrder))
            {
                $query = sprintf("SELECT question_order FROM temp_questions WHERE id=%s",pg_escape_string($this->id));
                $result = $GLOBALS['transaction']->query($query,115);
                
                $this->questionOrder = $result[0]['question_order'];
            }
            
            $oldQuestion = new TempQuestion();
            $oldQuestion->loadFromId($this->id);
            $oldQuestion->delete();
        }
        else
        {
            $oldILOIds = array();
            $query = sprintf("SELECT MAX(question_order) FROM temp_questions WHERE lesson_id=%s",pg_escape_string($this->lessonId));
            $result = $GLOBALS['transaction']->query($query);
            
            $maxQuestionOrder = $result[0]['max'];
            
            if(is_null($maxQuestionOrder))
            {
                $this->questionOrder = 1;
            }
            else
            {
                $this->questionOrder = $maxQuestionOrder+1;
            }
            
            $query = sprintf("SELECT nextval('temp_questions_id_seq')");
            $result = $GLOBALS['transaction']->query($query);
            
            $this->id=$result[0]['nextval'];
        }
        
        $query = sprintf("INSERT INTO temp_questions (id,content,answer,name,user_id,question_order,lesson_id) VALUES (%s,'%s','%s','%s',%s,%s,%s)",pg_escape_string($this->id),pg_escape_string($this->content),pg_escape_string($this->correctAnswer),pg_escape_string($this->name),pg_escape_string($userId),pg_escape_string($this->questionOrder),pg_escape_string($this->lessonId));
        $GLOBALS['transaction']->query($query,115);

        foreach($this->answerChoices as $key=>$choice)
        {
            $query = sprintf("INSERT INTO temp_answers (answerfield_id, answerchoice, answer_order) VALUES (%s,'%s',%s)",pg_escape_string($this->id),pg_escape_string($choice),pg_escape_string($key));
            $GLOBALS['transaction']->query($query,115);
        }
        
        $this->saveIlos($userId, $newILOIds,$oldILOIds);
        
        return true;
    }
    
    public function submitAnswer($submittedAnswer, $userId)
    {
        $query = sprintf("INSERT INTO user_answers (user_id, question_id, submitted_answer, time_answered) VALUES (%s,%s,%s,CURRENT_TIMESTAMP)",$userId,$this->id,$submittedAnswer);
        $GLOBALS['transaction']->query($query,117);
        
        return true;
    }
    
    public function getSubmittedAnswer($user_id)
    {
        $query = sprintf("SELECT submitted_answer, temp_questions.answer AS correct_answer FROM user_answers LEFT JOIN temp_questions ON id=question_id WHERE user_answers.user_id=%s AND temp_questions.id=%s",$user_id,$this->id);
        $result = $GLOBALS['transaction']->query($query);
        
        if($result==="none")
        {
            return false;
        }
        else
        {
            $submittedAnswer = array("submittedAnswer"=>$result[0]['submitted_answer'], "correctAnswer"=>$result[0]['correct_answer']);
            return $submittedAnswer;
        }
    }
    
    public function delete()
    {
        $contentHTML = $this->getHTML($this->content,$this->answerChoices);
        
        $deadILOs = Lesson::getILOIds($contentHTML);
        
        foreach($deadILOs as $ilo)
        {
            Ilo::killIlo($ilo);
        }
        
        $query = sprintf("DELETE FROM user_answers WHERE question_id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,116);
        
        $query = sprintf("DELETE FROM temp_answers WHERE answerfield_id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,116);
        
        $query = sprintf("DELETE FROM temp_questions WHERE id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,116);
        
        return true;
    }
    
    public function deleteUserAnswer($userId)
    {
        $query = sprintf("DELETE FROM user_answers WHERE question_id=%s and user_id=%s",pg_escape_string($this->id),pg_escape_string($userId));
        $GLOBALS['transaction']->query($query,118);
        
        return true;
    }
    
    public function getJSON()
    {
        return $this->json;
    }
}
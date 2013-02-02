<?php

require_once("classes/resources/LessonBoundContent.php");
require_once("classes/resources/Video.php");

class LessonAddition
{
    protected $type = null;
    protected $additionObject = null;
    
    # Constructor
    public function __construct($type)
    {
        $this->type = $type;
        
        switch($type)
        {
            case 'trainingmanual': case 'roleplay':
                $this->additionObject = new LessonBoundContent($this->type);
                break;
            case 'video':
                $this->additionObject = new Video();
                break;
            default:
                break;
        }
    }
    
    public function loadFromUri($uri,$dieOnFail=true)
    {
        return $this->additionObject->loadFromUri($uri,$dieOnFail);
    }
    
    public function loadFromPayload($payload,$path)
    {
        return $this->additionObject->loadFromPayload($payload,$path);
    }
    
    public function loadFromId($id,$dieOnFail=true)
    {
        return $this->additionObject->loadFromId($id,$dieOnFail);
    }
    
    public function buildJSON()
    {
        $this->additionObject->buildJSON();
    }
    
    public function save($userId,$notes=null)
    {
        return $this->additionObject->save($userId,$notes);
    }
    
    public function delete($userId=null)
    {
        return $this->additionObject->delete($userId);
    }
    
    public function getJSON()
    {
        return $this->additionObject->getJSON();
    }
    
    public function getId()
    {
        return $this->additionObject->getId();
    }
}
<?php

require_once("classes/resources/Material.php");

class Video
{
    protected $lessonId = null;
    protected $id = null;
    protected $content = null;
    protected $json = null;
    
    public function __construct()
	{
        
    }
    
    public function loadFromUri($uri,$dieOnFail=true)
	{
        $this->lessonId = Material::URIToId($uri,"lesson");
        $query = sprintf("SELECT id FROM lesson_additions WHERE type='video' AND name='video' and lesson_id=%s",$this->lessonId);
        
        if($dieOnFail)
        {
            $result = $GLOBALS['transaction']->query($query,129);
        }
        else
        {
            $result = $GLOBALS['transaction']->query($query);
            
            if($result==="none")
            {
                $this->content="none";
                return true;
            }
        }

        return $this->loadFromId($result[0]['id'],$dieOnFail);
    }
    
    public function loadFromPayload($payload,$path)
	{
        $this->lessonId = Material::URIToId($path,"lesson");
        $this->content = $payload->content;
        
        return true;
    }
    
    public function loadFromId($id,$dieOnFail=true)
    {
        $query = sprintf("SELECT * FROM lesson_additions WHERE id=%s",$id);
        $result = $GLOBALS['transaction']->query($query,130);
        
        $this->id = $id;
        $this->lessonId = $result[0]['lesson_id'];
        $this->content = $result[0]['content'];
        
        return true;
    }
    
    public function buildJSON()
    {
        $jsonArray = array("id"=>$this->id,"lessonId"=>$this->lessonId,"content"=>$this->content);
        
        $this->json=json_encode($jsonArray);
    }
    
    public function save($userId,$notes=null)
    {
        $query = sprintf("SELECT id FROM lesson_additions WHERE type='video' AND name='video' and lesson_id=%s",pg_escape_string($this->lessonId));
        $result = $GLOBALS['transaction']->query($query);
        
        if($result==="none")
        {
            $query = sprintf("INSERT INTO lesson_additions (type,lesson_id,content,name) VALUES ('video',%s,'%s','video')",pg_escape_string($this->lessonId),pg_escape_string($this->content));
            $GLOBALS['transaction']->query($query,131);
        }
        else
        {
            $query = sprintf("UPDATE lesson_additions SET content='%s' WHERE lesson_id=%s AND name='video'",pg_escape_string($this->content),pg_escape_string($this->lessonId));
            $GLOBALS['transaction']->query($query,131);
        }
        
        return true;
    }
    
    public function delete($userId=null)
    {
        $query = sprintf("DELETE FROM lesson_additions WHERE id=%s",pg_escape_string($this->id));
        $GLOBALS['transaction']->query($query,132);
        
        return true;
    }
    
    public function getJSON()
    {
        return $this->json;
    }
}
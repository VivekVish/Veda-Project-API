<?php

Class LessonRow
{
    
}

Class LessonHistory
{
	########################################################
	#### Member Variables ##################################
	########################################################
    private $revisionId = null;
    private $lessonId = null;
    
    private $lessonName = null;
    private $lessonDescription = null;
    private $lessonContent = null;
    private $lessonEditor = null;
    private $revisionTime = null;
    private $revisionNotes = null;
    
    ########################################################
	#### Constructor and main function #####################
	########################################################

	# Constructor
	public function __construct()
	{
		# Get DB handle
		$this->db = $GLOBALS['db'];
	}
    
    
	########################################################
	### Getters and Setters ################################
	########################################################
    
    # Set content 
	public function setRevisionId($revisionId)
	{
		$this->revisionId = $revisionId;
	}
    
    public function setLessonId($lessonId)
    {
        $this->lessonId = $lessonId;
    }
    
    public function setLessonName($lessonName)
    {
        $this->lessonName = $lessonName;
    }
    
    public function setLessonDescription($lessonDescription)
    {
        $this->lessonDescription = $lessonDescription;
    }
    
    public function setLessonContent($lessonContent)
    {
        $this->lessonContent = $lessonContent;
    }
    
    public function setLessonEditor($lessonEditor)
    {
        $this->lessonEditor = $lessonEditor;
    }
    
    public function setRevisionTime($revisionTime)
    {
        $this->revisionTime = $revisionTime;
    }
    
    public function setRevisionNotes($revisionNotes)
    {
        $this->revisionNotes = $revisionNotes;
    }
    
    # Get content 
	public function getRevisionId()
	{
		return $this->revisionId;
	}
    
    public function getLessonId()
    {
        return $this->lessonId;
    }
    
    public function getLessonName()
    {
        return $this->lessonName;
    }
    
    public function getLessonDescription()
    {
        return $this->lessonDescription;
    }
    
    public function getLessonContent()
    {
        return $this->lessonContent;
    }
    
    public function getLessonEditor()
    {
        return $this->lessonEditor;
    }
    
    public function getRevisionTime()
    {
       return $this->revisionTime;
    }
    
    public function getRevisionNotes()
    {
        return $this->revisionNotes;
    }
}
?>

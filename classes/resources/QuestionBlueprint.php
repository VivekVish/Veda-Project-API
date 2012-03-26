<?php

require_once("classes/resources/Material.php");
require_once("classes/resources/AnswerFieldBlueprint.php");

Class QuestionBlueprint {
   
    ##################################################
    ##### Member Variables ###########################
    ##################################################

    private $active = null; //boolean-is this question active? (no=question "deleted")
    private $answerfield_id = null; //I'm not sure whether to use this, the one above, or both. Basically, should I use a pointer to the object or store the object itself (or both)? a pointer will be more efficient, probably
    private $content = null; //string
    private $correct_answer = null;
    private $generic_params = null; //stuff describing things like range of random numbers in question
    private $id = null; //unique ID of this question
    private $lesson_id = null; //ties it to a certain lesson, but only to provide context for this question. Eg. "what is foam" would have different answers in chemistry and quantum mechanics 
    private $name = null; //like "easy addition question"
    private $repeatability_id = null; //references a certain Question_Repeatability, bound to Question_Repeatability.id. This tells the application how repeatable it is
    private $revision_date = null; //when this revision was saved
    private $revision_id = null; //there may be multiple questions with the same ID, but never multiple with the same revision AND same ID
    private $user_id = null; //who made this revision

    
    //not stored in database
    
    private $answerField = null; //NOT of type AnswerFieldBlueprint. Simply contains the JSON Array (not JSON formatted) from the AnswerField. Will be included in this object's JSON data. Filled when JSON data created.
    private $JSONData = null; //JSON data to be returned
    
    ##################################################
    ##### Member Functions ###########################
    ##################################################

    public function save() {
        $query = sprintf("SELECT revision_id FROM question_blueprint WHERE id=%s ORDER BY revision_date DESC LIMIT 1", pg_escape_string($this->id)); //get the previous revision ID for this QB
        $latest_revision_id = $GLOBALS['transaction']->query($query); //this is a STRING
        $latest_revision_id = intval($latest_revision_id) + 1; //latest_revision_id is now INCREMENTED

        $query = sprintf("INSERT INTO question_blueprint (active, answerfield_id, content, correct_answer, generic_params, id, lesson_id, name, repeatability_id, revision_id, user_id) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", pg_escape_string($this->active), pg_escape_string($this->answerfield_id), pg_escape_string($this->content), pg_escape_string($this->correct_answer), pg_escape_string($this->generic_params), pg_escape_string($this->id), pg_escape_string($this->lesson_id), pg_escape_string($this->name), pg_escape_string($this->repeatability_id), pg_escape_string($latest_revision_id), pg_escape_string($this->user_id)); //revision_date inserted automagically
        $result = $GLOBALS['transaction']->query($query);
        if ($result) {
            return true;
        }
        return false;
    }
    
     
    public function buildJSON() {//gets data, stores it as JSON
        $tempAnswerField = new AnswerFieldBlueprint();
        $tempAnswerField->loadFromId($this->answerfield_id);//loads the AnswerField in question
        $this->answerField = $tempAnswerField->getContent();//loads content of AnswerField
        $this->JSONData = json_encode(array("active" => $this->active, "answerField" => $this->answerField, "answerfield_id" => $this->answerfield_id, "content" => $this->content , "correct_answer" => $this->correct_answer , "generic_params" => $this->generic_params , "id" => $this->id , "lesson_id" => $this->lesson_id , "name" => $this->name , "repeatability" => $this->repeatability , "revision_date" => $this->revision_date , "revision_id" => $this->revision_id , "user_id" => $this->user_id));
    
      }

    public function getJSON() {//returns stored data
        return $this->JSONData;
    }

    ##################################################
    ##### Loader Functions ###########################
    ##################################################

    public function loadFromUri($uri) {
        $uriArr = explode("/", trim($uri, "/"));
        $this->id = $uriArr[3]; ///data/materials/questionBlueprints/{questionBlueprintId}
        $this->loadFromID($this->id);
        return true;
    }

    public function loadFromID($id) {
        $query = sprintf("SELECT * FROM question_blueprint WHERE id = %s", pg_escape_string($id)); //get all the stuff from the row where id=the id of the test we want
        $result = $GLOBALS["transaction"]->query($query); //store all the stuff from the row into an array called $results

        $this->active = $result["active"];
        $this->answerfield_id = $result["answerfield_id"];
        $this->content = $result["content"];
        $this->correct_answer = $result["correct_answer"];
        $this->generic_params = $result["generic_params"];
        $this->id = $result["id"];
        $this->lesson_id = $result["lesson_id"];
        $this->name = $result["name"];
        $this->repeatability_id = $result["repeatability_id"];
        $this->revision_date = $result["revision_date"];
        $this->revision_id = $result["revision_id"];
        $this->user_id = $result["user_id"];
        
        //not sure if using this:
        
        
    }

    ##################################################
    ##### Getters and Setters ########################
    ##################################################

    private function getActive() {
        return $this->active;
    }

    private function setActive($isActive) {
        $this->active = $isActive;
    }

    private function getAnswerField() {
        //answer field is not set, only answerfield_id is set. Answerfield is then loaded.
        return $this->answerField;
    }

    private function getAnswerfield_id() {
        return $this->answerfield_id;
    }

    private function setAnswerfield_id($answerfield_id) {
        $this->answerfield_id = $answerfield_id;
    }

    private function getContent() {
        return $this->content;
    }

    private function setContent($content) {
        $this->content = $content;
    }

    private function getCorrect_answer() {
        return $this->correct_answer;
    }

    private function setCorrect_answer($correct_answer) {
        $this->correct_answer = $correct_answer;
    }

    private function getGeneric_params() {
        return $this->generic_params;
    }

    private function setGeneric_params($generic_params) {
        $this->generic_params = $generic_params;
    }

    private function getId() {
        return $this->id;
    }

    private function setId($id) {
        $this->id = $id;
    }

    private function getLesson_id() {
        return $this->lesson_id;
    }

    private function setLesson_id($lesson_id) {
        $this->lesson_id = $lesson_id;
    }

    private function getName() {
        return $this->name;
    }

    private function setName($name) {
        $this->name = $name;
    }

    private function getRepeatability_id() {
        return $this->repeatability_id;
    }

    private function setRepeatability_id($repeatability_id) {
        $this->repeatability_id = $repeatability_id;
    }

    private function getRevision_id() {
        //revision ID is automatically set when INSERT occurs on save
        return $this->revision_id;
    }

    private function getUser_id() {
        return $this->user_id;
    }

    private function setUser_id($user_id) {
        $this->user_id = $user_id;
    }

  
}


  /* old version----------vvvv --do not use-- vvvv------------
     * 
      ##################################################
      ##### Member Variables ###########################
      ##################################################

      private $db = null;
      private $id = null;
      private $blueprint = null;//is this content? or something else?
      private $quizBlueprintId  = null;
      private $type_id = null;


      ##################################################
      ##### Member Functions ###########################
      ##################################################

      # Constructor
      public function __construct($blueprint = null)
      {
      $db = &$GLOBALS['db'];
      if (!empty($blueprint))
      {
      try
      {
      $blueprintObj = new SimpleXMLElement($blueprint);
      $this->id = (int)$blueprintObj->id;
      $this->type = (int)$blueprintObj->type;
      $this->schema = (string)$blueprintObj->schema;
      foreach ($blueprintObj->arguments as $argument)
      {
      $this->arguments[] = array("identifier" => (string)$argument->identifier, "type" => (int)$argument->type, "domain" => (string)$argument->domain);
      }
      }
      catch (Exception $e)
      {
      // Error handling
      }
      }
      }

      # Validate the question definition
      public function validate()
      {
      }

      # Save (always creates a new instance even if it is only being modified)
      public function save()
      {
      $query = sprintf("INSERT INTO question_blueprint (quiz_blueprint_id, type_id, content) VALUES (%s, %s, '%s')", pg_escape_string($this->quizBlueprintId), pg_escape_string($this->typeId), pg_escape_string($this->blueprint));
      $result = $this->db->exec($query);
      if ($result)
      {
      return true;
      }
      return false;
      }

      ##################################################
      ##### Getters and Setters ########################
      ##################################################

      # Get id
      public function getId()
      {
      return $this->id;
      }

      # Get quiz blueprint id
      public function getQuizBlueprintId()
      {
      return $this->quizBlueprintId;
      }

      # Get type id
      public function getTypeId()
      {
      return $this->typeId;
      }

      # Get blueprint
      public function getBlueprint()
      {
      return $this->blueprint;
      }

      # Set id
      public function setId($id)
      {
      $this->id = $id;
      }

      # Set Type ID
      public function setTypeId($typeId)
      {
      $this->typeId = $typeId;
      }

      # Set Quiz Blueprint Id
      public function setQuizBlueprintId($quizBlueprintId)
      {
      $this->quizBlueprintId = $quizBlueprintId;
      }

      # Set Blueprint
      public function setBlueprint($blueprint)
      {
      $this->blueprint = $blueprint;
      }
     */

?>



<?php
    require_once('classes/Error.php');

    class Material
    {
        protected $path = null;
        protected $id = null;
        protected $parentId = null;
        protected $childIds = null;
        protected $childData = array();
        protected $json = null;
        protected $description = null;
        protected $name = null;
        protected $active = null;
        
        // DESC: Converts a URI to an id
        // PARAMETER: $uri is a URI string
        // PARAMETER: $level is the URI level to be checked
        // RETURNS: integer id if successful, null otherwise
        public static function URIToId($uri,$level=null,$dieOnFail=true)
        {            
            $uri = trim($uri,"/");
            $uriArr = explode("/", $uri);
            
            foreach($uriArr as $key=>$value)
            {
                $uriArr[$key] = urldecode($value);
            }
            
            if(is_null($level))
            {
                switch(count($uriArr))
                {
                    case 3:
                        $level = "field";
                        break;
                    case 4:
                        $level = "subject";
                        break;
                    case 5:
                        $level = "course";
                        break;
                    case 6:
                        $level = "section";
                        break;
                    case 7:
                        $level = "lesson";
                        break;
                    case 9:
                        $level = "discussion";
                        break;
                }
            }
            
            switch($level)
            {
                case "field":
                    $query = sprintf("SELECT id FROM field WHERE name='%s'",
                                                        pg_escape_string($uriArr[FIELD_INDEX]));
                    break;
                case "subject":
                    $query = sprintf("SELECT id FROM subject WHERE name='%s' AND field_id=(SELECT id FROM field WHERE name='%s')",
                                                        pg_escape_string($uriArr[SUBJECT_INDEX]),
                                                        pg_escape_string($uriArr[FIELD_INDEX]));
                    break;
                case "course":
                    $query = sprintf("SELECT id FROM course WHERE name='%s' AND subject_id=(SELECT id FROM subject WHERE name='%s' AND field_id=(SELECT id FROM field WHERE name ='%s'))",
                    pg_escape_string($uriArr[COURSE_INDEX]),
                                                        pg_escape_string($uriArr[SUBJECT_INDEX]),
                    pg_escape_string($uriArr[FIELD_INDEX]));
                    break;
                case "section":
                    $query = sprintf("SELECT id FROM section WHERE name='%s' AND course_id=(SELECT id FROM course WHERE name='%s' AND subject_id=(SELECT id FROM subject WHERE name='%s' AND field_id=(SELECT id FROM field WHERE name ='%s')))",
                                                        pg_escape_string($uriArr[SECTION_INDEX]),
                                                        pg_escape_string($uriArr[COURSE_INDEX]),
                    pg_escape_string($uriArr[SUBJECT_INDEX]),
                    pg_escape_string($uriArr[FIELD_INDEX]));
                    break;
                case "lesson":
                    $query = sprintf("SELECT id FROM lesson WHERE name='%s' AND section_id=(SELECT id FROM section WHERE name='%s' AND course_id=(SELECT id FROM course WHERE name='%s' AND subject_id=(SELECT id FROM subject WHERE name='%s' AND field_id=(SELECT id FROM field WHERE name ='%s'))))",
                                                        pg_escape_string($uriArr[LESSON_INDEX]),
                                                        pg_escape_string($uriArr[SECTION_INDEX]),
                    pg_escape_string($uriArr[COURSE_INDEX]),
                    pg_escape_string($uriArr[SUBJECT_INDEX]),
                    pg_escape_string($uriArr[FIELD_INDEX]));
                    break;
                case "discussion":
                    if($uriArr[CONTENT_TYPE_INDEX] == "content")
                    {
                        $contentType = "lesson";
                    }
                    else if($uriArr[CONTENT_TYPE_INDEX] == "quiz")
                    {
                        $contentType = "quiz";
                    }
                    else
                    {
                        Error::generateError(99,"Content Type: {$uriArr[CONTENT_TYPE_INDEX]} and URI: $uri.");
                    }
                    
                    $query = sprintf("SELECT id FROM discussion WHERE element_type='%s' AND element_id=(SELECT id FROM %s WHERE name='%s' AND section_id=(SELECT id FROM section WHERE name='%s' AND course_id=(SELECT id FROM course WHERE name='%s' AND subject_id=(SELECT id FROM subject WHERE name='%s' AND field_id=(SELECT id FROM field WHERE name ='%s')))))",
                                    pg_escape_string($contentType),
                                    pg_escape_string($contentType),
                                    pg_escape_string($uriArr[LESSON_INDEX]),
                                    pg_escape_string($uriArr[SECTION_INDEX]),
                                    pg_escape_string($uriArr[COURSE_INDEX]),
                                    pg_escape_string($uriArr[SUBJECT_INDEX]),
                                    pg_escape_string($uriArr[FIELD_INDEX]));
                    
                    break;
                case "additions":
                    $query = sprintf("SELECT id FROM lesson_additions WHERE name='%s' AND lesson_id=(SELECT id FROM lesson WHERE name='%s' AND section_id=(SELECT id FROM section WHERE name='%s' AND course_id=(SELECT id FROM course WHERE name='%s' AND subject_id=(SELECT id FROM subject WHERE name='%s' AND field_id=(SELECT id FROM field WHERE name ='%s')))))",
									pg_escape_string($uriArr[LESSON_ADDITION_INDEX]),
                                    pg_escape_string($uriArr[LESSON_INDEX]),
									pg_escape_string($uriArr[SECTION_INDEX]),
                                    pg_escape_string($uriArr[COURSE_INDEX]),
                                    pg_escape_string($uriArr[SUBJECT_INDEX]),
                                    pg_escape_string($uriArr[FIELD_INDEX]));
                    break;
                default:
                    Error::generateError(62,"URI: $uri");
                    break;
            }
           
            if($dieOnFail)
            {
                $result = $GLOBALS['transaction']->query($query);
            }
            else
            {
                $result = $GLOBALS['transaction']->query($query,35);
            }

            if($result&&$result!="none")
            {
                return $result[0]["id"];
            }
            else
            {
                return null;
            }
        }
        
        public function getJSON()
        {
            return $this->json;
        }
        
        public function getId()
        {
            return $this->id;
        }
    }
?>

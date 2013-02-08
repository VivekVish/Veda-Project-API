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
        
        // DESC: Converts an id to a URI
        // PARAMETER: $id is a id number
        // PARAMETER: level is the URI level
        // RETURNS: string uri if successful, null otherwise
        public static function IdToURI($id,$level)
        {            
            switch($level)
            {
                case "field":
                    $query = sprintf("SELECT field.name AS field FROM field WHERE id=%s",
                                      pg_escape_string($id));
                    break;
                case "subject":
                    $query = sprintf("SELECT field.name AS field,
                                             subject.name AS subject
                                      FROM subject
                                      LEFT JOIN field ON subject.field_id=field.id
                                      WHERE subject.id=%s",
                                      pg_escape_string($id));
                    break;
                case "course":
                    $query = sprintf("SELECT field.name AS field,
                                             subject.name AS subject,
                                             course.name AS course
                                      FROM course
                                      LEFT JOIN subject on course.subject_id=subject.id
                                      LEFT JOIN field ON subject.field_id=field.id
                                      WHERE course.id=%s",
                                      pg_escape_string($id));
                    break;
                case "section":
                    $query = sprintf("SELECT field.name AS field,
                                             subject.name AS subject,
                                             course.name AS course,
                                             section.name AS section
                                      FROM section
                                      LEFT JOIN course on section.course_id=course.id
                                      LEFT JOIN subject on course.subject_id=subject.id
                                      LEFT JOIN field ON subject.field_id=field.id
                                      WHERE section.id=%s",
                                      pg_escape_string($id));
                    break;
                case "lesson":
                    $query = sprintf("SELECT field.name AS field,
                                             subject.name AS subject,
                                             course.name AS course,
                                             section.name AS section,
                                             lesson.name AS lesson
                                      FROM lesson
                                      LEFT JOIN section on lesson.section_id=section.id
                                      LEFT JOIN course on section.course_id=course.id
                                      LEFT JOIN subject on course.subject_id=subject.id
                                      LEFT JOIN field ON subject.field_id=field.id
                                      WHERE lesson.id=%s",
                                      pg_escape_string($id));
                    break;
                default:
                    Error::generateError(152,"ID: $id");
                    break;
            }

            $result = $GLOBALS['transaction']->query($query);

            if(isset($result[0]["lesson"]))
            {
                return sprintf("/data/material/%s/%s/%s/%s/%s",$result[0]["field"],$result[0]["subject"],$result[0]["course"],$result[0]["section"],$result[0]["lesson"]);
            }
            else if(isset($result[0]["section"]))
            {
                return sprintf("/data/material/%s/%s/%s/%s",$result[0]["field"],$result[0]["subject"],$result[0]["course"],$result[0]["section"]);
            }
            else if(isset($result[0]["course"]))
            {
                return sprintf("/data/material/%s/%s/%s",$result[0]["field"],$result[0]["subject"],$result[0]["course"]);
            }
            else if(isset($result[0]["subject"]))
            {
                return sprintf("/data/material/%s/%s",$result[0]["field"],$result[0]["subject"]);
            }
            else if(isset($result[0]["field"]))
            {
                return sprintf("/data/material/%s",$result[0]["field"]);
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

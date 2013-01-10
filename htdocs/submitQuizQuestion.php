<?php

# Includes
require_once("../config/main.inc.php");

# Get request data
$question_id = (int)trim($_REQUEST['question_id']);
$answer_id = (int)trim($_REQUEST['answer_id']);
$attempt = (int)trim($_REQUEST['attempt_id']);

# Save attempt
$return = false;
$sql = sprintf("INSERT INTO quiz_submitted_answers (user_id, attempt, quiz_question_id, quiz_questions_answers_id) VALUES (1, %s, %s, %s)", pg_escape_string($attempt), pg_escape_string($question_id), pg_escape_string($answer_id));
if ($result = pg_query($sql))
{
	$return = ++$question_id; 
}

# Send response
print ($return);
exit();

?>

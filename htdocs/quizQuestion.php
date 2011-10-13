<?php

# Includes
require_once("../config/main.inc.php");

# Get id
$question_id = trim($_REQUEST['id']);

# Get question
$sql = sprintf("SELECT * FROM quiz_questions WHERE id = '%s'", pg_escape_string($question_id));
$result = pg_query($sql);
$row = pg_fetch_assoc($result);
$return['question'] = $row['content'];

# Get question answers
$sql = sprintf("SELECT * FROM quiz_questions_answers WHERE quiz_question_id = '%s'", pg_escape_string($question_id));
$result = pg_query($sql);
while ($row = pg_fetch_assoc($result))
{
	$return['answers'][$row['id']] = $row['content'];
}

# Send back content
$return = json_encode($return);
header("Content-Type: application/json");
print_r($return);

?>

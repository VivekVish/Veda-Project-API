<?php

# Includes
require_once("../config/main.inc.php");

# Get Request Vars
$user_id = $_REQUEST['user_id'];
$quiz_id = $_REQUEST['quiz_id'];
$attempt_id = $_REQUEST['attempt_id'];

# Build response
$sql = sprintf("
				SELECT 
					qsa.quiz_question_id AS question_id,
					qsa.quiz_questions_answers_id AS submitted_answer_id,
					qqca.quiz_questions_answers_id AS correct_answer_id
				FROM 
					quiz_submitted_answers AS qsa 
					LEFT JOIN quiz_questions_correct_answers AS qqca ON (qsa.quiz_question_id = qqca.quiz_questions_id) 
				WHERE 
					qsa.user_id = %s 
					AND qsa.quiz_question_id IN (SELECT id FROM quiz_questions WHERE quiz_id = %s)
					AND qsa.attempt = %s", 
					pg_escape_string($user_id), 
					pg_escape_string($quiz_id), 
					pg_escape_string($attempt_id)
				);

$result = pg_query($sql);
$rows = pg_fetch_all($result);
$num_questions = count($rows);
$correct = 0;
foreach ($rows as $question)
{
	if ($question['submitted_answer_id'] == $question['correct_answer_id'])
	{
		$correct++;
	}
	else
	{
		$sql = "SELECT content FROM quiz_questions WHERE id = {$question['question_id']}";
		$result = pg_query($sql);
		$row = pg_fetch_assoc($result);
		$question_text = $row['content'];
	
		$sql = "SELECT content FROM quiz_questions_answers WHERE id = {$question['submitted_answer_id']}";
		$result = pg_query($sql);
		$row = pg_fetch_assoc($result);
		$submitted_answer_text = $row['content'];

		$sql = "SELECT content FROM quiz_questions_answers WHERE id = {$question['correct_answer_id']}";
		$result = pg_query($sql);
		$row = pg_fetch_assoc($result);
		$correct_answer_text = $row['content'];
	
		$corrections[] = array("question" => $question_text, "submitted_answer" => $submitted_answer_text, "correct_answer" => $correct_answer_text);
	}
}

$score = $correct / $num_questions * 100;
$results['score'] = round($score, 2);
if (!empty($corrections))
{
	$results['corrections'] = $corrections;
}

# Clear results
$sql = "DELETE FROM quiz_submitted_answers";
$result = pg_query($sql);

# Send response
header("Content-Type: application/json");
print_r(json_encode($results));
exit();

?>

<?php

Class StaticQuiz
{
	## Member Variables ##
	private $quizQuestions = null;
	private $quizQuestionsAnswers = null;

	## Member Functions ##

	# Constructor
	public function __construct()
	{
	}

	# Process
	public function process()
	{
		$action = $this->getAction();
		switch (strtolower($action))
		{
			case 'get':
			case 'put':
			case 'post':
			case 'delete':
		}
		return false;
	}

	# Save (adds quiz or updates quiz)
	public function save()
	{
	}

	# Delete self
	# Get quiz data
}

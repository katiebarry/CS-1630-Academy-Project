<?
	require("../glue.php");
	init("form_process");

	if($_SESSION["usertype"] != "admin")
	{
		return_to(HOME_DIR);
	}
	if(isset($_POST['singleClass']))
	{
		createSingleClass();
	}
	elseif(isset($_POST['multipleClasses']))
	{
		createMultipleClasses();
	}

	return_to(HOME_DIR."pages/create_class.php");
	
	function createSingleClass()
	{
		global $db;
		
		$class_name = sqlite_escape_string(trim($_POST['class_name']));
		$instructor_email = sqlite_escape_string(trim($_POST['instructor_email']));
		$room = sqlite_escape_string(trim($_POST['room']));
		$description = sqlite_escape_string(trim($_POST['description']));
		
		$query = "select user_id from User where email = '$instructor_email'";
		$results = $db->arrayQuery($query);
		if(empty($results))
		{
			$_SESSION["creation-message-error"] = "Error inserting class into database: instructor email not found";
			return false;
		}
		else
		{
			$instructor_id = $results[0]['user_id'];
			$query = "insert into Class values(NULL, '$class_name', '$instructor_id', '$instructor_email', '$room', '$description')";
			
			@$result = $db->queryExec($query, $error);
			
			if (empty($result) || $error)
			{
				$_SESSION["creation-message-error"] = "Error inserting class into database: $error";
				return false;
			}
			else
			{
				$class_id = $db->lastInsertRowid();
				
				@$results = $db->queryExec("insert into Enrollment values ('$class_id','$instructor_id')", $error);
				
				if (empty($results) || $error)
				{
					$_SESSION["creation-message-error"] = "Error enrolling instructor in course: $error";
					return false;
				}
				else
				{
					$_SESSION["creation-message"] = "Class successfully created.";
					return true;
				}
			}
		}
	}
		
	function createMultipleClasses()
	{
		global $db;

		if($_FILES['uploadedfile']['error'] != 0)
		{
			$_SESSION["creation-message-error"] = "Error creating class: File upload fail";
			return false;
		}

		$filename = basename($_FILES['uploadedfile']['name']);
		
		//creates the CSVUploads dir
		if (!is_dir(CLASS_PATH."CSVUploads"))
		{
			mkdir(CLASS_PATH."CSVUploads");
		}

		//moves the .csv file to the uploads dir
		if(!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], CLASS_PATH . "CSVUploads/" . $filename))
		{
        	$_SESSION["creation-message-error"] = "Error uploading .csv file.";
        	return false;
		}

		$lines = file(CLASS_PATH . "CSVUploads/" . $filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		if (empty($lines))
		{
			$_SESSION["creation-message-error"] = "Error creating class: No entries found in file.";
        	return false;
		}

		if (count(explode(",",$lines[0])) != 4)
		{
			$_SESSION["creation-message-error"] = "Error creating class: Data format in .csv file is invalid.";
        	return false;	
		}

		$success = true;

		foreach ($lines as $line)
		{
			$line = trim($line);
			$linesplit = explode(",",$line);

			if (!insert_class($linesplit))
			{
				if (!isset($_SESSION["creation-message-error"])): $_SESSION["creation-message-error"] = ""; endif;
				$_SESSION["creation-message-error"] .= "Error adding the following line: $line.<br>";
				$success = false;
			}
		}

		if ($success)
		{

			if(count($lines) == 1)
			{
				$_SESSION["creation-message"] = "1 class successfully created.";
			} 
			else
			{
				$_SESSION["creation-message"] = count($lines) . " classes successfully created.";	
			} 
		}

		return $success;
	}

	function insert_class($linesplit)
	{
		global $db;
		$teacher_email = trim($linesplit[1]);

		$query = "select user_id, usertype from User where email = '$teacher_email'";
		$results = $db->arrayQuery($query);
		if(empty($results))//email is not found in the User's database
		{
			$_SESSION["creation-message-error"] = "Error creating class: instructor with email \"$email\" not found";
			return false;
		}
		elseif ($results[0]['usertype'] != "teacher") 
		{
			$_SESSION["creation-message-error"] = "Error creating class: instructor email not valid - $email";
			return false;
		}
		else
		{
			$teacher_id = $results[0]['user_id'];
		}

		$class_name = trim($linesplit[0]);
		$room = trim($linesplit[2]);
		$description = nl2br(trim($linesplit[3]));
		$query = "insert into Class values (NULL, '$class_name', '$teacher_id', '$teacher_email', '$room', '$description')";
		@$result = $db->queryExec($query, $error);
		if (empty($result) || $error)
		{
			$_SESSION["creation-message-error"] = "Error inserting class into database: $error";
			return false;
		}

		$class_id = $db->lastInsertRowid();

		@$results = $db->queryExec("insert into Enrollment values ('$class_id','$instructor_id')", $error);
		if (empty($results) || $error)
		{
			$_SESSION["creation-message-error"] = "Error enrolling instructor in course: $error";
			return false;
		}

		return true;
	}
?>
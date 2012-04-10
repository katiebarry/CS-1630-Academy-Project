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
	
	function createSingleClass(){
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
			$result = $db->queryExec($query, $error);
			if (empty($result))
			{
				$_SESSION["creation-message-error"] = "Error inserting class into database: $error";
				return false;
			}
			else
			{
				$class_id = $db->lastInsertRowid();
				$results = $db->queryExec("insert into Enrollment values ('$class_id','$instructor_id')", $error);
				if (empty($results))
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
		
	function createMultipleClasses(){
		global $db;
		$filename = $_FILES['uploadedfile']['tmp_name'];
		if($_FILES['uploadedfile']['name'] == null || $_FILES['uploadedfile']['error'] != 0){
			$_SESSION["creation-message-error"] = "Error creating class: File upload fail";
			return false;
		}
		
		$file = fopen($filename, "r") or exit("Unable to open file!");
		//parse file into arrays
		while(!feof($file))
  		{
  			$line = fgets($file);
  			$linesplit = explode("|", $line);
			$lines[] = $linesplit;
			if(count($linesplit) != 4){
				$_SESSION["creation-message-error"] = "Error creating class: Wrong format in file.";
				return false;
			}
  		}
		fclose($file);
		for($i = 0; $i < count($lines); $i++){
			$email = trim($lines[$i][1]);
			//check if email exists
			//else set error, break, return
			$query = "select user_id,usertype from User where email = '$email'";
			$results = $db->arrayQuery($query);
			if(empty($results)){//email is not found in the User's database
				$_SESSION["creation-message-error"] = "Error creating class: instructor email not found - $email";
				return false;
			}else{
				if($results[0]['usertype'] != "teacher"){//the email is not a teacher's email
					$_SESSION["creation-message-error"] = "Error creating class: instructor email not valid - $email";
					return false;
				}
				else{//no problem
					$lines[$i][] = $results[0]['user_id'];
				}
			}
		}
		
		for($i = 0; $i < count($lines); $i++){
			$className = trim($lines[$i][0]);
			$instructor_email = trim($lines[$i][1]);
			$room = trim($lines[$i][2]);
			$description = nl2br(trim($lines[$i][3]));
			$instructor_id = $lines[$i][4];
			
			$query = "insert into Class values(NULL, '$className', '$instructor_id', '$instructor_email', '$room', '$description')";
			$result = $db->queryExec($query, $error);
			if (empty($result))
			{
				$_SESSION["creation-message-error"] = "Error inserting class into database: $error";
				return false;
			}
			else
			{
				$class_id = $db->lastInsertRowid();
				$results = $db->queryExec("insert into Enrollment values ('$class_id','$instructor_id')", $error);
				if (empty($results))
				{
					$_SESSION["creation-message-error"] = "Error enrolling instructor in course: $error";
					return false;
				}
			}
		}
		if(count($lines) == 1) $_SESSION["creation-message"] = "1 Class successfully created.";
		else $_SESSION["creation-message"] = count($lines) . " Classes successfully created.";
		return true;
	}
?>
<?
	require("../glue.php");
	init("form_process");

	//This page processes the various actions requested by modify_users.php, including enrolling, deleting, and changing passwords
	
	if($_SESSION["usertype"] != "admin")
	{
		return_to(HOME_DIR);
	}
	
	$requested_action = $_POST['modifySubmit']; //check if this exits?
	$checked =  $_POST['check'];
	if(empty($checked))
	{
		//This should not happen due to client-side checking.
		$_SESSION["modify-message-error"] = "No users selected!";
		return_to(HOME_DIR."pages/modify_users.php");	
	}
	else
	{
		$count = count($checked);
		//Now let's go case by case
		if($requested_action == "Enroll")
		{
			$class_id = sqlite_escape_string($_POST['class_id']);

			for($i=0; $i < $count; $i++)
			{
				//enroll this particular student in the class
				$user_id = $checked[$i];
				$query = "insert into Enrollment values('$class_id', '$user_id')";
				
				@$result = $db->queryExec($query, $error);
				if (empty($result) || $error)
				{
					if (!isset($_SESSION["modify-message-error"])): $_SESSION["modify-message-error"] = ""; endif;
					$_SESSION["modify-message-error"] .= "Error enrolling user $user_id into class $class_id: $error";
					if ($i != $count-1)
					{
						$_SESSION["modify-message-error"] .= "<br>";
					}
				}
				else
				{
					if (!isset($_SESSION["modify-message"])): $_SESSION["modify-message"] = ""; endif;
					$_SESSION["modify-message"] .= "User $user_id successfully enrolled in $class_id.";
					if ($i != $count-1)
					{
						$_SESSION["modify-message"] .= "<br>";
					}
				}
			}

			return_to(HOME_DIR."pages/modify_users.php");		
		}
		elseif($requested_action == "Delete Users")
		{
			for($i=0; $i < $count; $i++)
			{
				//This is the man we wanted! Delete him!
				$user_id = $checked[$i];
				$query = "delete from User where user_id = $user_id";
				
				@$result = $db->queryExec($query, $error);
				if (empty($result) || $error)
				{
					if (!isset($_SESSION["modify-message-error"])): $_SESSION["modify-message-error"] = ""; endif;
					$_SESSION["modify-message-error"] .= "Error deleting user: $error";
					if ($i != $count-1)
					{
						$_SESSION["modify-message-error"] .= "<br>";
					}
				}
				else
				{
					if (!isset($_SESSION["modify-message"])): $_SESSION["modify-message"] = ""; endif;
					$_SESSION["modify-message"] .= "Successfully deleted user $user_id.";
					if ($i != $count-1)
					{
						$_SESSION["modify-message"] .= "<br>";
					}
				}
			}

			return_to(HOME_DIR."pages/modify_users.php");
		}
		elseif($requested_action == "Change Passwords")
		{
			//php seems to insist the blank boxes have values, so we have to check ourselves
			$all_password =  $_POST['password'];

			foreach ($all_password as $key => $password)
			{
				if(strlen(trim($all_password[$key])) < 1)
				{
					unset($all_password[$key]);
				}
			}

			if(empty($all_password))
			{
				//This should not happen due to client-side checking.
				$_SESSION["modify-message-error"] = "No new passwords given.";
				return_to(HOME_DIR."pages/modify_users.php");
			}

			$count = 0;

			end($all_password);
			$last = key($all_password);
			reset($all_password);

			foreach ($all_password as $key => $raw_pass)
			{
				$user_id = $checked[$count];
				$salt = make_salt();
				$pass = crypt($raw_pass, '$5$'.$salt);
				$query = "update User set password = '$pass', salt = '$salt' where user_id = '$user_id'";				
				
				$result = $db->queryExec($query, $error);
				if (empty($result) || $error)
				{
					if (!isset($_SESSION["modify-message-error"])): $_SESSION["modify-message-error"] = ""; endif;
					$_SESSION["modify-message-error"] .= "Error updating password: $error";
					if ($key != $last)
					{
						$_SESSION["modify-message-error"] .= "<br>";
					}
				}
				else
				{
					if (!isset($_SESSION["modify-message"])): $_SESSION["modify-message"] = ""; endif;
					$_SESSION["modify-message"] .= "Successfully updated password for user $user_id.";
					if ($key != $last)
					{
						$_SESSION["modify-message"] .= "<br>";
					}
				}
				
				$count++;
			}

			return_to(HOME_DIR."pages/modify_users.php");
		}
	}

	function make_salt()
	{
		$characters = "abcdefghijklmnopqrstuvwxyz0123456789";
		$length = strlen($characters);
		$salt = "";

		for ($x=0; $x<16; $x++)
		{

			$salt .= $characters[rand(0,$length-1)];
		}

		return $salt;
	}
?>
<?
	require("../glue.php");
	init("page");
	get_header();

	$username = $_SESSION["username"];
	$user_id = $_SESSION["user_id"];
	$usertype = $_SESSION["usertype"];
	
	if (!isset($_GET["class_id"]))
	{
		echo "<em>No course selected...</em>";
	}
	else
	{
		$selected = sqlite_escape_string($_GET["class_id"]);
		$results = $db->arrayQuery("select * from Class where class_id = '$selected';");
		if (!isset($results) || empty($results))
		{
			echo "<em>Selected course is invalid...</em>";
		}
		else
		{
			$course_name = isset($results[0]["class_name"]) ? $results[0]["class_name"] : "selected course";
			$instructor_email = isset($results[0]["instructor_email"]) ? $results[0]["instructor_email"] : "N.A.";
			$room = isset($results[0]["room"]) ? $results[0]["room"] : "N.A.";
			$description = isset($results[0]["description"]) ? $results[0]["description"] : "N.A.";
			$results = $db->arrayQuery("select * from Enrollment where user_id = '$user_id' and class_id = '$selected';");
			if (!isset($results) || empty($results)) //user is not in the course
			{
				echo "<em>Sorry $username, your are not currently enrolled in $course_name...</em>";
			}
			else
			{
				$assignments = $db->arrayQuery("select * from Assignment where class_id = '$selected' and is_open = 1;");
				if (empty($assignments) && $_SESSION["usertype"] == "student")
				{
					echo "<em>No assignments currently available for $course_name...</em>";
				}
				else //assignments are available
				{
					$assignments = $db->arrayQuery("select * from Assignment where class_id = '$selected';");
					echo "<h1>Assignments for $course_name</h1>";
					echo "Instructor Email: $instructor_email<br>" ;
					echo "Room: $room<br>";
					echo "Description: $description<br>";
					if ($usertype == "teacher")
					{
						if (isset($_SESSION["creation-message"]))
						{
							echo "<div class='message-wrapper'><div id='class-creation-message' class='info message'>".$_SESSION["creation-message"]."<br></div></div>";
							unset($_SESSION["creation-message"]);
							?>
								<script>
									$('.message-wrapper').click(function(){
										$(this).hide("slow");
									})
								</script>
							<?
						}
						elseif (isset($_SESSION["creation-message-error"]))
						{
							echo "<div class='message-wrapper'><div id='class-creation-message' class='warning message'>".$_SESSION["creation-message-error"]."<br></div></div>";
							unset($_SESSION["creation-message-error"]);
							?>
								<script>
									$('.message-wrapper').click(function(){
										$(this).hide("slow");
									})
								</script>
							<?
						}

						if(isset($_SESSION["delete_success"]))
						{
							echo "<div class='message-wrapper'><div id='class-deletion-message' class='info message'>".$_SESSION["delete_success"]."<br></div></div>";
							unset($_SESSION["delete_success"]);
							?>
								<script>
									$('.message-wrapper').click(function(){
										$(this).hide("slow");
									})
								</script>
							<?

						}
						elseif(isset($_SESSION["delete_failure"]))
						{
							echo "<div class='message-wrapper'><div id='class-deletion-message' class='warning message'>".$_SESSION["delete_failure"]."<br></div></div>";
							unset($_SESSION["delete_failure"]);
							?>
								<script>
									$('.message-wrapper').click(function(){
										$(this).hide("slow");
									})
								</script>
							<?
						}
					}
					echo "<ol id='assignment-list'>";
					foreach ($assignments as $assignment)
					{
						if ($assignment["is_open"] == 1)
						{
							?><li><a href="view_assig.php?class_id=<?= $assignment["class_id"] ?>&amp;assignment_id=<?= $assignment["assignment_id"] ?>"><?= $assignment["title"] ?></a></li><?	
						}
						if ($_SESSION["usertype"] == "teacher" && $assignment["is_open"] == 0)
						{
							?><li><em>(<a href="view_assig.php?class_id=<?= $assignment["class_id"] ?>&amp;assignment_id=<?= $assignment["assignment_id"] ?>"><?= $assignment["title"] ?></a>)</em></li><?		
						}
						
					}
					if ($usertype == "teacher")
					{
						echo "<li><a href='create_assig.php?class_id=$selected'>[+]</a></li>";
					}
					echo "</ol>";
				}
			}		
		}
		
		
	}
	get_footer();
?>


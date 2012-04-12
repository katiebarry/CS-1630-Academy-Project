<?
	require("../glue.php");
	//This page is a modification of modify_users.php to handle classes similarly.
	//Starting out with just viewing and deleting classes. More features will be added as they are decided that they are needed.
	init("page");
	//enqueue_script($filename)
	get_header();
	
?>
<!-- Using the copy of DataTables hosted on Microsoft CDN to simplify file structure. -->
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.8.2/css/jquery.dataTables.css">
 
<!-- jQuery -->
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.7.1.min.js"></script>
 
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.8.2/jquery.dataTables.min.js"></script>

<?
/* PLEASE CAREFULLY READ THESE COMMENTS!
 *
 * FIRST: specify any $_GET variables you need to have when getting to this page.  For example, if it is the page for viewing an assignment, assume that $_GET will have a variable representing the assignment ID.  That way, you know which assignment to query from the database.  TELL ME HERE WHAT YOU WANT THE VARIABLE TO BE NAMED.  This ensures that the pages will link together correctly.
 * use $_SESSION["username"] and $_SESSION["usertype"] to segregate the components of the page (i.e. if ($username != "admin") etc.)
 * use $db to make database calls
 * make calls to get ALL relevant information on the page loaded into PHP variables
 * CAREFULLY DOCUMENT the contents of these variables (e.g. $assignments is an array and each element is an array representing an assignment.  In this array, "id" => the ID of the course, "name" => the name of the course, etc)
 * Do not worry about having too much information loaded - it is easy to show only parts of it or show it in chunks with HTML/JavaScript.  Just worry about getting it on the page.
 *
 * FORMS: If this page is a data page and requires a form, please either 1. specify the fields the form needs to have (i.e. inputs: text "name", text "email", password "password").  This includes what type of input it is and WHAT THE NAME IS.  This is critical to making sure it lines up with get/post on the next page.  If you are comfortable writing HTML, simply write the form.  If any information from your PHP variables needs to be included, please either included it or leave careful instructions.
 * MAKE ABSOLUTELY SURE you use the add_token() method in every form or your form will not work
 *
 * FINALLY: don't forget to check if things exist?  Use the (bool ? A : B) notation to accomplish this.  For example.  $result = ((isset($var) && !empty($var)) ? $var : "" )
 *
 */
 
 

 $usertype = $_SESSION["usertype"];
//We start out with checking that the user is an admin and rejecting them if they are not.
if($usertype != "admin")
{
	error_message("User does not have access to this feature...");
	get_footer();
	die;
}
else{ //Now we pull the data for the table from the database.
	$results = $db->arrayQuery("select class_id, class_name, instructor_id, username, instructor_email from Class, User where User.user_id = Class.instructor_id");
	//The above SQL query is long, but it essentially justs takes the Class table but with the instructor's name added to each row.
	
}
if ($usertype == "admin")
{
	if (isset($_SESSION["modify-classes-message"]))
	{
		echo "<div id='class-modify-classes-message' class='info message'>".$_SESSION["modify-classes-message"]."<br></div>";
		unset($_SESSION["modify-classes-message"]);
		?>
			<script>
				setTimeout(function(){
					$('#class-modify-classes-message').hide("slow");
				}, 2000);
			</script>
		<?
	}
	elseif (isset($_SESSION["modify-classes-message-error"]))
	{
		echo "<div id='class-modify-classes-message' class='warning message'>".$_SESSION["modify-classes-message-error"]."<br></div>";
		unset($_SESSION["modify-classes-message-error"]);
		?>
			<script>
				setTimeout(function(){
					$('#class-modify-classes-message').hide("slow");
				}, 2000);
			</script>
		<?	
	}
}	

?>

<!-- Now we have the HTML for displaying the table. After this works, add on DataTables -->
<h1>Modify Classes</h1>
<!-- TODO: create page process_modify_classes.php and function (below) submit_modify_classes -->

<form id="modify_classes" method="post" action="process_modify_classes.php" >
	
	<input type="submit" name="modify_classesSubmit" onclick="return clickDelete()" value="Delete Users"/>&nbsp;
	<input type="reset" value="Reset">
	<? add_token(); ?>
	<br />
	<br />
	<table id="classes_table">
		<thead>
			<tr>
				<th>Class Name</th>
				<th>Instructor Name</th>
				<th>Email</th>
				<th>Select</th>
			</tr>
		</thead>
		<tbody>
		<!-- It is now time to go to php to populate the table with info from $results -->
		<?php
		foreach ($results as $entry)
		{ 
		  //It's easier to echo this out if it is stored this way.
		  $id = $entry['class_id'];
		  echo "<tr>";
		  //First we have the class's name and instructor's name and email.
		  echo "<td>{$entry['class_name']}</td><td>{$entry['username']}</td><td>{$entry['instructor_email']}</td>";
		  //Now a checkbox for deleting the class.
		  echo "<td><input type='checkbox' name='check[]' id='check_$id' value='$id' onclick='checkClick($id)' /></td>"; 
		  echo "</tr>";     
		} 
		?>
		
		
		
		</tbody>
	</table>
</form>
	

<script type="text/javascript">
	//This bit here starts up DataTables
	$(document).ready(function(){
	  $('#classes_table').dataTable({
		"aoColumnDefs": [
		  { "asSorting": [ "asc", "desc" ], "aTargets": [ 0, 1, 2 ] },
		  { "asSorting": [ ], "aTargets": [ 3 ] },
		  { "sWidth": "35%", "aTargets": [ 0 ] },
		   { "sWidth": "25%", "aTargets": [ 1 ] }
		] //the sizes probably need to be changed
	  });
	});

	var checkedCount = 0;
	
	//Keeps track of how many checkboxes are checked, so that we can refuse to submit a page with none checked.
	function checkClick(id_num)
	{
		var id = "check_".concat(id_num);
		var checkbox = document.getElementById(id);
		if(checkbox.checked == true)
		{
			checkedCount++;
		}
		else
		{
			checkedCount--;
		}
	}
	
	//If the Delete button is hit, there should be a class selected and a confirmation box should pop up.
	function clickDelete()
	{
		if(checkedCount <= 0)
		{
			alert("Please select a class before deleting.")
			return false;
		}
		else
		{
			var ok = confirm("Select ok to confirm deletion");
			return ok;
		}
		return true;
	}

</script>

<? get_footer(); ?>
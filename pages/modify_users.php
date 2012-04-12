<?
	require("../glue.php");
	
	init("page");
	enqueue_script("jquery.dataTables.min.js");
	get_header();
 

 $usertype = $_SESSION["usertype"];
//We start out with checking that the user is an admin and rejecting them if they are not.
if($usertype != "admin")
{
	error_message("User does not have access to this feature...");
	get_footer();
	die;
}
else
{ //Now we pull the data for the table from the database.
	//This should be enough to initially populate the table.
	$results = $db->arrayQuery("select user_id, username, email, usertype from User");
	$classes = $db->arrayQuery("select class_id, class_name from Class");
	//Change needed:
	//May need another request to verify changes, such as having the enrollment table to approve/deny enrollment changes
	
}
if ($usertype == "admin")
{
	if (isset($_SESSION["modify-message"]))
	{
		echo "<div class='message-wrapper'><div id='class-modify-message' class='info message'>".$_SESSION["modify-message"]."<br></div></div>";
		unset($_SESSION["modify-message"]);
		?>
			<script>
				$('.message-wrapper').click(function(){
					$(this).hide("slow");
				})
			</script>
		<?
	}
	
	if (isset($_SESSION["modify-message-error"]))
	{
		echo "<div class='message-wrapper'><div id='class-modify-message' class='warning message'>".$_SESSION["modify-message-error"]."<br></div></div>";
		unset($_SESSION["modify-message-error"]);
		?>
			<script>
				$('.message-wrapper').click(function(){
					$(this).hide("slow");
				})
			</script>
		<?	
	}
}	

?>

<!-- Now we have the HTML for displaying the table. After this works, add on DataTables -->
<h1>Modify Users</h1>
<!-- TODO: create page process_modify_users.php (or do it another way, inline?) and function (below) submit_modify_users -->
<!-- onsubmit="return submit_modify_users()" -->

<form id="modify_users" method="post" action="process_modify_users.php" >
	<select name="class_id" id="class_name" style='width: 130px;'>
		<option value="">Classes...</option>
		<? 
		for($i = 0; $i < count($classes); $i++){
			echo "<option value='". $classes[$i]['class_id']."'>". $classes[$i]['class_name']. "</option>\n";
		}
		?>
	</select>
	<input type="submit" name="modifySubmit" onclick="return clickEnroll();" value="Enroll"/>&nbsp;
	<input type="submit" name="modifySubmit" onclick="return clickDelete();" value="Delete Users"/>&nbsp;
	<input type="submit" name="modifySubmit" onclick="return clickPassword();" value="Change Passwords"/>&nbsp;
	<input type="reset" value="Reset">
	<? add_token(); ?>
	<br />
	<br />
	<div id='table-container'>
		<table id="users_table">
			<thead>
				<tr>
					<th>Select</th>
					<th>Name</th>
					<th>Email</th>
					<th>Type</th>
					<th>Password</th>
				</tr>
			</thead>
			<tbody>
			<!-- It is now time to go to php to populate the table with info from $results -->
			<?php
			//$arrayLength = count($results); //Delete if not used at end
			//for($i = 0; $i <= $arrayLength; $i = $i + 6)
			foreach ($results as $entry)
			{ 
				//admin's can't modify other admins
				if ($entry['usertype'] == "admin" && $entry['user_id'] != $_SESSION['user_id'] && $entry['username'] != "Root Admin") continue;
			  
			  //It's easier to echo this out if it is stored this way.
			  $id = $entry['user_id'];
			  echo "<tr>";
			  //Now a checkbox for applying the given action to this person.
			  echo "<td><input type='checkbox' name='check[]' id='check_$id' value='$id' onclick='checkClick($id)' /></td>";
			  //First we have the particular user's name and usertype.
			  echo "<td>{$entry['username']}</td><td>{$entry['email']}</td><td>{$entry['usertype']}</td>";
			  //Now we get a text input box for inputing a new password. 
			  echo "<td><input type='text' name='password[]' id='password_$id' style='width: 100px;' title='$id'/></td>"; 
			  echo "</tr>\n";     
			} 
			?>
			
			
			
			</tbody>
		</table>
	</div>
</form>
	

<script type="text/javascript">
	//This bit here starts up DataTables
	$(document).ready(function(){
	  $('#users_table').dataTable({
		"aoColumnDefs": [
		  { "asSorting": [ "asc", "desc" ], "aTargets": [ 1, 2, 3 ] },
		  { "asSorting": [ ], "aTargets": [ 4 ] },
		  { "sWidth": "35%", "aTargets": [ 0 ] },
		  { "sWidth": "25%", "aTargets": [ 1 ] },
		  { 'bSortable': false, 'aTargets': [ 0 ] }
		]
	  });
	  $('#users_table_filter').after("<br>");

	  $('input[type=checkbox]').each(function(index){
			if($(this).is(':checked')){
				$(this).removeAttr('checked'); //unchecks it
			}
		});

	  	$('input[type=text]').each(function(index){
			$(this).val("");
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
	
	//If the Enroll button is hit, make sure a class is selected and at least one user is selected.
	function clickEnroll()
	{
		if($('#class_name').val() == "" || $('#class_name').val() == null){
			alert("Please select a class in which to enroll the students.");
			return false;
		}
		if(checkedCount <= 0)
		{
			alert("Please select a user to enroll.")
			return false;
		}
		return true;
	}
	
	//If the Delete button is hit, there should be a user selected and a confirmation box should pop up.
	function clickDelete()
	{
		if(checkedCount <= 0)
		{
			alert("Please select a user before deleting.")
			return false;
		}
		else
		{
			var ok = confirm("Select ok to confirm deletion");
			return ok;
		}
		return true;
	}
	
	//If the Change Password button is hit, none of the relevant password boxes should be blank.
	function clickPassword()
	{
		var ret = true;

		//check that passwords are provided for all check boxes
		$('input[type=checkbox]').each(function(index){
			if($(this).is(':checked')){
				var pwd_id = "#password_" + $(this).val();
				if ($(pwd_id).val() == ""){
					alert("Please specify a new password for all selected entries.");
					ret = false;
				}
			}
		});


		$('input[type=text]').each(function(index){
			var name = $(this).attr("name");
			if (typeof(name) !== "undefined" && name == "password[]" && $(this).val() != ""){
				var box_id = "#check_" + $(this).attr("title");
				if (!($(box_id).is(':checked'))){
					alert("Please check all boxes for which you would like to specify a new password.");
					ret = false;
				}
			} 
		});

		if(checkedCount <= 0)
		{
			alert("Please select a password to change.")
			ret = false;
		}

		return ret;
	}
	

</script>

<? get_footer(); ?>
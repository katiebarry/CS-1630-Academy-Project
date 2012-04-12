<?
	require("../glue.php");
	init("page");
	//enqueue_script($filename)
	get_header();

	$usertype = $_SESSION["usertype"];

	if($_SESSION["usertype"] != "admin")
	{
		error_message("User does not have access to this feature...");
		get_footer();
		die;
	}
	else{
		$results = $db->arrayQuery("select user_id, username, email from User where usertype = 'teacher'");
	}

	if ($usertype == "admin")
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
		if (isset($_SESSION["creation-message-error"]))
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
	}	
	
?>
	<h1>Create Class</h1>
	<hr>
	<h2>Create Single Class</h2>
	<form id="create_class" method="post" action="process_create_class.php" onsubmit="return submit_create_class()">
	<table>
	<tr>
		<td>Class Name:</td><td><input type="text" name="class_name" id="class_name" style='width: 325px;'/></td>
	</tr>
	<tr>
		<td>Instructor Email:</td>
		<td>
			<select name="instructor_email" id="instructor_email" style='width: 330px;'>
				<option value=""></option>
				<? 
				for($i = 0; $i < count($results); $i++){
					echo "<option value='". $results[$i]['email']."'>". $results[$i]['email']. "</option>";
				}
				?>
			</select> 
		</td>
	</tr>
	<tr>
		<td>Room:</td><td><input type="text" name="room" id="room" style='width: 325px;'/></td>
	</tr>
	<tr>
		<td>Description:</td><td><textarea name='description' id='description' rows=10 cols=40 style="resize: vertical;"></textarea></td>
	</tr>
	<tr>
		<td><input type="submit" value="Submit" name="singleClass"/>&nbsp;
		<input type="reset" value="Reset"/></td>
		<td><? add_token(); ?></td>
	</tr>
	</table>
	</form>
	<br>
	<hr>
	<h2>Create Multiple Classes</h2>
	<form enctype="multipart/form-data" id='multi' name="csvform" action="process_create_class.php" method="POST" >
		<input type="hidden" name="MAX_FILE_SIZE" value="300000000"/>
		Choose a file to upload: <input id='file' name="uploadedfile" type="file" /><br />
		<input type="submit" value="Upload File" name="multipleClasses" />
		<? add_token(); ?>
	</form>
	<em><div style='font-size: 12px; margin-top: 15px;'>File must be formated as (class name,instructor email,room,description).
		If you <br>are exporting data from Excel, please make sure to remove the column headers.</div></em>
	<br>
	
	<script type="text/javascript">

	function submit_create_class(){
		
		if($('#class_name').val() == ""){
			alert("Class name cannot be empty");
			return false;
		}
		if($('#instructor_email').val() == "" || $('#instructor_email').val() == null){
			alert("Instructor email cannot be empty");
			return false;
		}
		if($('#room').val() == ""){
			var c = confirm("Leave the Room empty?");
			if(!c) return false;
		}
		if($('#description').val() == ""){
			var c = confirm("Leave the Description empty?");
			if(!c) return false;
		}
		return true;
	}
	
	$(document).ready(function(){
		$('#multi').submit(function(){
			if ($('#file').val() == ""){
				alert("Please specify a file to upload.");
				return false;
			}
			else{
				var filename = $('#file').val();
				var ext = filename.slice(-4);
				if (ext != ".csv"){
					alert("File must be a .csv file");
					return false;
				}
				else{
					return true;
				}
			}
		});
	});
	
</script>

	
<? get_footer(); ?>
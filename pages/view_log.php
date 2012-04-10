<?
	require("../glue.php");
	init("page");
	enqueue_script("jquery.dataTables.min.js");
	get_header();

	$results = $db->arrayQuery("select * from Log");

	?>
	<div id='table-container'>
	<? if ($_SESSION["usertype"] == "admin"): ?>
		<form method="POST" name="logform" action="process_view_log.php">
		<? add_token(); ?>
	<? endif; ?>
	<table id="logtable">	
		<thead>
			<tr>
				<? if ($_SESSION["usertype"] == "admin"): ?>
					<th>Select</th>
				<? endif; ?>
				<th>Course</th>
				<th>Assig</th>
				<th>User Name</th>
				<th>Submission Time</th>
				<th>Successful</th>
				<th>Comment</th>
			</tr>
		</thead>
		<tbody>
	<?
	for($i=0;$i<count($results);$i++)
	{
		$res = $results[$i];
		$loaded[$i] = $res['submission_id'];
		$res_class_name = $db->arrayQuery("select class_name from Class where class_id=$res[course_id]");
		$res_assig_name = $db->arrayQuery("select title from Assignment where assignment_id=$res[assignment_id]");
		$class_name = $res_class_name[0]['class_name'];
		$assig_name = $res_assig_name[0]['title'];
		if($res['successful'])
			$success = "True";
		else
			$success = "False";
		echo "<tr>";
		if ($_SESSION["usertype"] == "admin"): echo "<td><input type='checkbox' name='LogId_$loaded[$i]'/>"; endif;
		echo "<td>$class_name</td>";
		echo "<td>$assig_name</td>";
		echo "<td>$res[username]</td>";
		echo "<td>$res[submission_time]</td>";
		echo "<td>$success</td>";
		echo "<td>$res[comment]</td>";
		echo "</tr>";
	}
		if(isset($loaded))
			$_SESSION['logloaded'] = $loaded;

	?>
		</tbody>
	</table><br><br>
	<? if ($_SESSION["usertype"] == "admin"): ?>
		<input type="submit" Value="Delete Selected" Name="delete"/>&nbsp;
		<input type="button" Value="Toggle All" name="toggle" onclick="toggle_all()"/>
		</form>
	<? endif; ?>
	<br><br>
	</div>
<?


?>
	<script type="text/javascript">

		$(document).ready(function() {
    		$('#logtable').dataTable({});
    		$('#logtable_filter').after("<br>");
		});

		<? if ($_SESSION["usertype"] == "admin"): ?>
			function toggle_all()
			{
				$('input[type=checkbox]').each(function(index) {
					if($(this).is(':checked'))
					{
						$(this).removeAttr('checked'); //unchecks it
					}
					else
					{
						$(this).attr('checked','checked'); //turns them on
					}
				});
			}
		<? endif; ?>


	</script>

<? get_footer(); ?>
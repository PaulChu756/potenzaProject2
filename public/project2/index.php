<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

$connection = mysqli_connect($host, $user, $password);
if(!$connection) {die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);

function getPeople($connection)
{
	$output = '';
	$sql = "SELECT * FROM People";
	$result = mysqli_query($connection,$sql);
	while($row = mysqli_fetch_array($result)) 
	{
		$output .= "<option value=" . $row['id'] . ">" . $row['firstname'] . "</option>";
	}
	return $output;
}
//don't need to close connection anymore because exc function and ends
//$connection->close();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Project 1 with BootStrap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8"/>
	<script src="jquery.js"></script>
	<link href="css/bootstrap.css" rel="stylesheet">
	<script src="js/bootstrap.js"></script>
  </head>
  
	<body>
		<div class = "container">
			<br><center><img class = "img-responsive" src = "stormtrooper.jpg" alt = "Stormtrooper" width = "200" height="200"></center>
			<center><h1>Follow ze steps </h1></center>
			<p class = "text-center">
			<br>Step 1: Must initialize init.php, cli php init.php.
			<br>Step 2: Add a person to the Database
			<br>Step 3: Add a visit to a person
			<br>Step 4: Select a human!
			</p>	
		</div>
	</body>
</html>

<!--Add Person -->
<form action = "people.php" method = "get">
<input type ="submit" class = "btn btn-warning" value = "Add a Person" style = "float: right;"/>
</form>

<!--Add Visit -->
<form action = "visit.php" method = "get">
<br><br>
<input type = "submit" class = "btn btn-success" value = "Add a Visit" style = "float: right;"/>
</form>

<center>
<form>
	<br><br>Select a human and learn where they're from and favor food
	<br><br><select name="Name" id="Name">
	<option value="">Select a human:</option>
	<?php 
	echo getPeople($connection);
	?> 
	</select>
	<div class="row" id="showPerson">
	<?php
	echo getPeople($connection);
	?>
	</div>
</center>
</form>
<div id = "form"><center><br><strong>Selected person info will be here</strong></center></div>

<script>
$(document).ready(function(){
	$('#Name').change(function(){
		var getInfo = $(this).val();
		$.ajax({
				url: "api.php",
				type: "POST",
				// Key | Value 
				data:{personID:getInfo},
				success: function(data)
				{
					$('#showPerson').html(data);
				}
				// type of data we expect back
				//dataType : "json",
		});
	});
});
		
/*
Pure javascript, doesn't use Jquery at all
function getInfo(str)
{
	if(str == "")
	{
		return document.getElementById("form").innerHTML = "";
	}
	else 
	{
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			// readyState 4 means complete && 200 means complete
			if(this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("form").innerHTML = this.responseText;
			}
		};
		xmlhttp.open("GET","api.php?q="+str, true);
		xmlhttp.send();
	}
}
*/
</script>
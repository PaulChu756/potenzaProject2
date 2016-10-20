<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Project 1 with BootStrap</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	 <link href="css/bootstrap.css" rel="stylesheet">
	 <script src="js/bootstrap.js"></script>
  </head>

  <body>
  	<div class = "container-fluid">
	<center>
	<h1>Follow ze steps </h1>
	<p>
	<br>Step 1: Initialize Database
	<br>Step 2: Add a person to the Database
	<br>Step 3: Add a visit to a person 
	</p>
	</center>
	</div>
  </body>
</html>

<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);
$sql = "SELECT id, firstname FROM People";
$result = mysqli_query($connection,$sql);
$connection->close();
?>

<!-- Start Init -->
<form action = "init.php" method = "get">
<input type ="submit" value = "Initialize Database" style = "float: right;"/>
</form>

<!--Add Person -->
<form action = "people.php" method = "get">
<br><br>
<input type ="submit" value = "Add a Person" style = "float: right;"/>
</form>

<!--Add Visit -->
<form action = "visit.php" method = "get">
<br><br>
<input type = "submit" value = "Add a Visit" style = "float: right;"/>
</form>

<!--This is the drop down box with names-->
<form action = "form.php" method = "post">
<br><br><center>Select a human and learn where they're from and favor food</center>
	<br><center><select name="Name">
	<?php 
	while($row = mysqli_fetch_array($result)) {
	echo "<option value='" . $row['id'] . "'>" . $row['firstname'] . "</option>";}?> 
	</select>
		
	<input type = "submit" value = "Submit"/>
</center>
</form>

<script>
/*
// Javascript
var str = ""
function selectPerson(str)
{
        var xmlhttp = new XMLHttpRequest();

        xmlhttp.onreadystatechange = function()
        {
            // readyState 4 means complete && 200 means complete
	        if(this.readyState == 4 && this.status == 200) 
	        {
	            console.log("Went through");
	            //document.getElementByID(grab ID).innerHTML = this.responseText;
	        }
	        
	        else
                {
                        console.log("error");
                }
        };

        xmlhttp.open("GET","api.php?q="+str, true);
        xmlhttp.send();
}
*/
</script>

<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);

if($_SERVER['REQUEST_METHOD'] === 'POST') 
{
	// Name input
	if(isset($_POST['Name']))
	{
		// Give id the input number
		$id = $_POST['Name'];
		
		$resultSql = "SELECT p.firstname, s.statename, p.food
		FROM Visits v
		INNER JOIN People p ON v.p_id = p.id
		INNER JOIN States s ON v.s_id = s.id
		WHERE v.p_id =" . $id;

		$query = mysqli_query ($connection, $resultSql) or die(mysqli_error($connection));
		$row2 = mysqli_fetch_array($query);
	
		$firstName = $row2["firstname"];
		$stateName = $row2["statename"];
		$foodName = $row2["food"];

		if(!empty($firstName) && !empty($stateName) && !empty($foodName))
		{
			echo "<br> The human you select is : " . $firstName;
		
			echo "<br> The state they're visited : " . $stateName;
			while($row3 = mysqli_fetch_array($query))
			{
				$stateName = $row3["statename"];
				echo "<br> The state they're visited : " . $stateName;				
			}

			echo "<br> Their favor food is : " . $foodName;
		}
		
		else
		{
			echo "<br> You need to add a visit";
		}
	}
}
$connection->close();
?>
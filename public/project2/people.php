<!DOCTYPE html>
<html>
  <head>
    <title>Project 1 with BootStrap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="css/bootstrap.css" rel="stylesheet">
	<script src="js/bootstrap.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  </head>
</html>

<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

// Connect and check
$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);

// define variables to be all empty
$firstNameError = $lastNameError = $foodError = "";
$firstNameEnter = $lastNameEnter = $foodEnter = "";

// When submit is pressed, info send to database
if($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$firstNameEnter = $_POST["firstName"];
	$lastNameEnter = $_POST["lastName"];
	$foodEnter = $_POST["food"];

	if(!empty($firstNameEnter) && !empty($lastNameEnter) && !empty($foodEnter))
	{
	// Insert values into table
	$sql = "INSERT INTO People (firstname, lastname, food) 
	VALUES('$firstNameEnter', '$lastNameEnter', '$foodEnter')";
		// Check if insert is good
		if($connection->query($sql) === FALSE)
		{
		echo "Error: " . $sql . "<br>" . $connection->error;
		}
	}
	else 
	{
	$firstNameError = "First Name is required";
	$lastNameError = "Last Name is required";
	$foodError = "Food is required NOW!";
	}
}
$connection->close();
?>

<!--Add Person into Table-->
<form action = "people.php" method = "post">
<center><span class="error">* required field. </span><br><br>
First Name: <input type = "text" name = "firstName">
<span class = "error">* <?php echo $firstNameError;?></span><br><br>
Last Name: <input type = "text" name = "lastName">
<span class = "error">* <?php echo $lastNameError;?></span><br><br>
Favorite Food: <input type = "text" name = "food">
<span class = "error">* <?php echo $foodError;?></span><br><br>
<input type = "submit" value = "Submit" class = "btn btn-success"/>
</center>
</form>

<!--Return button-->
<form action = "index.php" method = "get">
<input type = "submit" value = "Return back to form" class = "btn btn-success" style = "float: right;"/>
</form>

<?php
echo "<br>First name entered: " . $firstNameEnter . "<br>";
echo "<br>Last name entered: " . $lastNameEnter . "<br>";
echo "<br>Favorite food: " . $foodEnter . "<br>";
?>


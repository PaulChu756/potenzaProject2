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

	if(!empty($firstNameEnter) && !empty($lastNameEnter) && !empty($foodEnter)){
	// Insert values into table
	$sql = "INSERT INTO People (firstname, lastname, food) 
	VALUES('$firstNameEnter', '$lastNameEnter', '$foodEnter')";
		// Check if insert is good
		if($connection->query($sql) === FALSE){
		echo "Error: " . $sql . "<br>" . $connection->error;
		}
	}
	else {
	$firstNameError = "First Name is required";
	$lastNameError = "Last Name is required";
	$foodError = "Food is required NOW!";
	}
}
$connection->close();
?>

<!--Add Person into Table-->
<form action = "people.php" method = "post">
<center>Add a person to the table</center><br>
<center><span class="error">* required field. </span></center><br><br>
First Name: <input type = "text" name = "firstName">
<span class = "error">* <?php echo $firstNameError;?></span><br><br>
Last Name: <input type = "text" name = "lastName">
<span class = "error">* <?php echo $lastNameError;?></span><br><br>
Favorite Food: <input type = "text" name = "food">
<span class = "error">* <?php echo $foodError;?></span><br><br>
<input type = "submit" value = "Submit"/>
</form>

<!--Return button-->
<form action = "form.php" method = "get">
<input type = "submit" value = "Return back to form" style = "float: right;"/>
</form>

<?php
//Print out first and last names
echo "First name entered: " . $firstNameEnter . "<br>";
echo "Last name entered: " . $lastNameEnter . "<br>";
echo "Favorite food: " . $foodEnter . "<br>";
?>


<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

//Connection and check connect
$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);

//Select names
$humanSql = "SELECT id, firstname FROM People";
// Because I query two times, possible of two input
$humanResult = mysqli_query($connection,$humanSql);

//Select states
$stateSql = "SELECT id, statename FROM States";
// Because I query two times, possible of two input
$stateResult = mysqli_query($connection,$stateSql);

// select both id's from people and statesd
$visitSql = "SELECT People.firstname, States.statename 
	FROM Visits v
	INNER JOIN People p ON v.p_id = p.id
	INNER JOIN States s ON v.s_id = s.id";

$connection->close();
?>

<form action = "visit.php" method = "post">

<!--Select human-->
<br>Select a human and learn their name
<br><select name="humanName">
<?php 
while($row = mysqli_fetch_array($humanResult)) {
echo "<option value='" . $row['id'] . "'>" . $row['firstname'] . "</option>";}?> 
</select>

<!--Select States-->
<br><br><br><br><br><br>Select a state 
<br><select name="stateName">
<?php 
while($row = mysqli_fetch_array($stateResult)) {
echo "<option value='" . $row['id'] . "'>" . $row['statename'] . "</option>";}?> 
</select>

<!--Add visit-->
<br><br><br><br><br><br><br><br><br><br><br>Add a visit to the table
<br><span class="error">* required field. </span><br><br>
Date Visited:<br>
Format: YYYY/MM/DD<br>
Example: 1994/07/14<br>
<input type = "text" name = "visit">
<span class = "error">* <?php echo $visitError;?></span><br><br>
<input type = "submit" value = "Enter ze data"/>
</form>

<!--Return button-->
<form action = "form.php" method = "get">
<input type = "submit" value = "Return back to form" style = "float: right;"/>
</form>

<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

//Connection and check connect
$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);

// Put visit error blank
$visitError = $visitEnter = "";

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$personEnter = $_POST["humanName"];
	$stateEnter = $_POST["stateName"];
	$visitEnter = $_POST["visit"];
		
	if(!empty($visitEnter))
	{
	$visitSql = "INSERT INTO Visits(p_id, s_id, date_visited)
	VALUES('$personEnter', '$stateEnter', '$visitEnter')";

		// Check if insert is good
		if($connection->query($visitSql) === TRUE){
		echo $personEnter . "<br>";
		echo $stateEnter . "<br>";
		echo $visitEnter;
		
		}
		else{
		echo "Error: " . $visitSql . "<br>" . $connection->error;
		}
	}
	else{
	$visitError = "Data visit is required";
	}
}
$connection->close();
?>


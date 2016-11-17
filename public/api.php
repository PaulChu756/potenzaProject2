<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";
$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);

$requestURI = parse_url($_SERVER['REQUEST_URI']);
$segments = explode('/', trim($requestURI['path'], '/'));
$apiVars = [];

$i = 2;
while($i < count($segments)) 
{    
	if($segments[$i+1]) 
	{  
		$apiVars[$segments[$i]] = $segments[$i+1];  
		$i += 2;    
	}
	else 
	{  
		$apiVars[$segments[$i]] = null;  
		$i++;    
	}
		 
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch($apiVars)
{
	case "people":
		if($requestMethod === "GET")
		{
			//$id = intval($_GET["personID"]);
			//getPerson($id);
			getPerson();
		}
		elseif($requestMethod === "POST")
		{
			insertPerson();
		}
		else
		{
			die();
		}
		break;
	case "states":
		if($requestMethod === "GET")
		{
			getStates();
		}
		elseif($requestMethod === "POST")
		{
			insertVisit();
		}
		else
		{
			die();
		}
		break;
}

header('application/json');
echo json_encode($apiVars);
die();

// Select all people or select a person
function getPerson($id=0)
{
	global $connection;
	$resultSql = "SELECT * FROM People";

	if($id != 0)
	{
		$resultSql.=" WHERE id=". $id ." LIMIT 1";
	}
	
	$response = array();
	$query = mysqli_query ($connection, $resultSql) or die(mysqli_error($connection));
	while($row = mysqli_fetch_array($query, true))
	{
		$response[] = $row;
	}
	header('Content-Type: application/json');
	echo json_encode($response);
}

//select all states
function getStates()
{
	global $connection;
	$stateSql = "SELECT * FROM States";

	$response = array();
	$stateQuery = mysqli_query($connection,$stateSql) or die(mysqli_error($connection));
	while($row = mysqli_fetch_array($stateQuery, true))
	{
		$response[] = $row;
	}
	header('Content-Type: application/json');
	echo json_encode($response);
}

// haven't test
function insertPerson()
{
	global $connection;
	// define variables to be all empty
	$firstNameError = $lastNameError = $foodError = "";
	$firstNameEnter = $lastNameEnter = $foodEnter = "";

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

	/*
	echo "<br>First name entered: " . $firstNameEnter . "<br>";
	echo "<br>Last name entered: " . $lastNameEnter . "<br>";
	echo "<br>Favorite food: " . $foodEnter . "<br>";
	*/
}

// haven't test
function insertVisit()
{
	global $connection;
	$visitError = $visitEnter = "";

	$personEnter = $_POST["humanName"];
	$stateEnter = $_POST["stateName"];
	$visitEnter = $_POST["visit"];
		
	if(!empty($visitEnter))
	{
	$visitSql = "INSERT INTO Visits(p_id, s_id, date_visited)
	VALUES('$personEnter', '$stateEnter', '$visitEnter')";

		// Check if insert is good
		if($connection->query($visitSql) === FALSE)
		{
			echo "Error: " . $visitSql . "<br>" . $connection->error;
		}
	}
	else
	{
		$visitError = "Data visit is required";
	}

	/*
	echo "Person entered : " . $personEnter . "<br>";
	echo "State entered : " . $stateEnter . "<br>";
	echo "Date entered : " . $visitEnter . "<br>";
	*/
}

$connection->close();
?>

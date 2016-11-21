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

header('Content-Type: application/json');
echo(json_encode($apiVars, JSON_PRETTY_PRINT));
die();

$requestMethod = $_SERVER["REQUEST_METHOD"];
if($requestMethod === $_GET)
{
	
}

// Select all people/select a person
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
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//select all states/select a state
function getStates($id=0)
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
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//select all visits/select a visit
function getVisits($id=0)
{
	global $connection;
	$visitSql = "SELECT * FROM Visits";

	$response = array();
	$visitQuery = mysqli_query($connection,$visitSql) or die(mysqli_error($connection));
	while($row = mysqli_fetch_array($visitQuery, true))
	{
		$response[] = $row;
	}
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//insert a Person
function insertPerson()
{
	global $connection;

	$firstNameEnter = $_POST["firstName"];
	$lastNameEnter = $_POST["lastName"];
	$foodEnter = $_POST["favoriteFood"];

	if(!empty($firstNameEnter) && !empty($lastNameEnter) && !empty($foodEnter))
	{
		// Insert values into table
		$sql = "INSERT INTO People (firstname, lastname, food) 
		VALUES ('$firstNameEnter', '$lastNameEnter', '$foodEnter')";

		// Check if insert is good
		if(mysqli_query($connection, $sql))
		{
			echo "You have added a friend" . $firstNameEnter . $lastNameEnter . $foodEnter;
		}
	}
	
	else 
	{
		echo "Error: " . $sql . "<br>" . $connection->error;
	}
}

// Insert a Visit
function insertVisit()
{
	global $connection;

	$personEnter = $_POST["humanNameDropDown"];
	$stateEnter = $_POST["stateNameDropDown"];
	$visitEnter = $_POST["visit"];
		
	$visitSql = "INSERT INTO Visits(p_id, s_id, date_visited)
	VALUES('$personEnter', '$stateEnter', '$visitEnter')";

	if($connection->query($visitSql) == FALSE)
	{
		echo "Error: " . $visitSql . "<br>" . $connection->error;
	}

	else
	{
		echo "You have added a visit";
	}
}

$connection->close();

// ------Comment Section-----

/*
$requestMethod = $_SERVER["REQUEST_METHOD"];
$peopleRequest = "people";
$stateRequest = "states";

switch($stateRequest)
{
	case "people":
		if($requestMethod == "GET")
		{
			$id = intval($_GET["personID"]);
			getPerson($id);
		}
		elseif($requestMethod == "POST")
		{
			insertPerson();
		}
		else
		{
			echo "People failed";
			die();
		}
		break;

	case "states":
		if($requestMethod == "GET")
		{
			getStates();
		}
		elseif($requestMethod == "POST")
		{
			insertVisit();
		}
		else
		{
			echo "States failed";
			die();
		}
		break;
}
*/ 
?>


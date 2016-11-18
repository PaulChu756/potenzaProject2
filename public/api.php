<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";
$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);

/*
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

header('application/json');
echo json_encode($apiVars);
die();
*/

$requestMethod = $_SERVER["REQUEST_METHOD"];
$peopleRequest = "people";
$stateRequest = "states";

switch($peopleRequest)
{
	case "people":
		if($requestMethod == "GET")
		{
			//$id = intval($_GET["personID"]);
			//getPerson($id);
			getPerson();
		}
		elseif($requestMethod == "POST")
		{
			insertPerson();
		}
		else
		{
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
			die();
		}
		break;
}

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

	$firstNameEnter = $_POST["firstName"];
	$lastNameEnter = $_POST["lastName"];
	$foodEnter = $_POST["favoriteFood"];

	// Insert values into table
	$sql = "INSERT INTO People (firstname, lastname, food) 
	VALUES('$firstNameEnter', '$lastNameEnter', '$foodEnter')";

	if($connection->query($sql) === FALSE)
	{
		echo "Error: " . $sql . "<br>" . $connection->error;
	}
	
	else 
	{
		echo "You have added a friend" . $firstNameEnter . $lastNameEnter . $foodEnter;
	}
}

// haven't test
function insertVisit()
{
	global $connection;

	$personEnter = $_POST["humanName"];
	$stateEnter = $_POST["stateName"];
	$visitEnter = $_POST["visit"];
		
	$visitSql = "INSERT INTO Visits(p_id, s_id, date_visited)
	VALUES('$personEnter', '$stateEnter', '$visitEnter')";

	if($connection->query($visitSql) === FALSE)
	{
		echo "Error: " . $visitSql . "<br>" . $connection->error;
	}

	else
	{
		echo "You have added a visit";
	}
}

$connection->close();
?>

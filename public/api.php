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

$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == "GET")
{
	if(array_key_exists("people", $apiVars))
	{
		if($apiVars["people"] == null)
		{
			getPerson();
		}
		elseif($apiVars["people"] !== null)
		{
			getPerson($apiVars['people']);
		}
		else
		{
			die();
		}
	}
	elseif(array_key_exists("visits", $apiVars))
	{
		if($apiVars["visits"] == null)
		{
			// get visits
		}
		elseif($apiVars["visits"] !== null)
		{
			//get visit by id $apiVars["visits"];
		}
		else
		{
			die();
		}
	}
	elseif(array_key_exists("states", $apiVars))
	{
		if($apiVars["states"] == null)
		{
			//gets States();
		}
		else
		{
			die();
		}
	}
	else
	{
		// return people, visits and states
	}
}
elseif($requestMethod == "POST")
{
	if($apiVars["people"] == null)
	{
		insertPerson();
	}
	elseif($apiVars["visits"] == null)
	{
		insertVisit();
	}
	else
	{
		die();
	}
}
else
{
	die();
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
			$id = intval($_GET["personID"]);
			getPerson($id);
			//getPerson();
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

// haven't test
function insertVisit()
{
	global $connection;

	$personEnter = $_POST["humanName"];
	$stateEnter = $_POST["stateName"];
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
?>

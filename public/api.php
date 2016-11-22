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
$segments = explode('/', $requestURI['path']);
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
$requestMethod = $_SERVER["REQUEST_METHOD"];

//GET request
if($requestMethod === "GET")
{
	//check people exist
	if(array_key_exists("people", $apiVars))
	{	
		//get all people
		if($apiVars["people"] == null)
		{
			getPeople($id);
		}
		// get a person
		elseif($apiVars["people"] != null)
		{
			getPeople($apiVars["people"]);
		}
		else
		{
			var_dump("ERROR");
		}
	}
	//check states exist
	elseif(array_key_exists("states", $apiVars))
	{
		//get states
		if($apiVars["states"] == null)
		{
			getStates($id);
		}
		//get a state
		elseif($apiVars["states"] != null)
		{
			getStates($apiVars["states"]);
		}
		else
		{
			var_dump("ERROR");
		}
	}
	//check visits exist
	elseif(array_key_exists("visits", $apiVars))
	{
		//get visits
		if($apiVars["visits"] == null)
		{
			getVisits($id);
		}
		// get a visit
		elseif($apiVars["visits"] != null)
		{
			getVisits($apiVars["visits"]);
		}
		else
		{
			var_dump("ERROR");
		}
	}
	else
	{
		var_dump("You have entered an invalid GET request");
	}
}
// POST request
elseif($requestMethod === "POST")
{
	//check if people exist
	if(array_key_exists("people", $apiVars))
	{
		insertPerson();
	}
	//check if visits exist
	elseif(array_key_exists("visits", $apiVars))
	{
		insertVisit();
	}
	else
	{
		var_dump("ERROR: post request");
	}
}
else
{
	var_dump("ERROR: fourth wall");
}

// Select all people/select a person /api/people
function getPeople($id=0)
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

//select all states/select a state /api/states
function getStates($id=0)
{
	global $connection;
	$stateSql = "SELECT * FROM States";

	if($id != 0)
	{
		$stateSql.=" WHERE id=". $id ." LIMIT 1";
	}

	$response = array();
	$stateQuery = mysqli_query($connection,$stateSql) or die(mysqli_error($connection));
	while($row = mysqli_fetch_array($stateQuery, true))
	{
		$response[] = $row;
	}
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//select all visits/select a visit /api/visits
function getVisits($id=0)
{
	global $connection;
	$visitSql = "SELECT * FROM Visits";

	if($id != 0)
	{
		$visitSql.=" WHERE id=". $id ." LIMIT 1";
	}

	$response = array();
	$visitQuery = mysqli_query($connection,$visitSql) or die(mysqli_error($connection));
	while($row = mysqli_fetch_array($visitQuery, true))
	{
		$response[] = $row;
	}
	header('Content-Type: application/json');
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//insert a Person //api/person
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

// Insert a Visit //api/visit
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
?>


<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";
$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
$connection->select_db($database);

$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod === "GET")
{
	// Get People
	if($_GET["personID"])
	{
		if(!empty($_GET["personID"]))
		{
			// select one person
			var_dump($_GET);
			$id = intval($_GET["personID"]);
			getPerson($id);
		}
		else
		{
			echo "Ugh";
			// select everyone
			var_dump($_GET);
			getPerson();
		}
	}
	
	// Get States
	if($_GET["stateID"])
	{
		getStates();
		if(empty($_GET["stateID"]))
		{
			var_dump($_GET);
			getStates();
		}
	}
}

if($requestMethod === "POST")
{
	var_dump($_POST);
	if($_POST["insertPerson"])
	{
		insertPerson();	
	}
	elseif($_POST["insertVisit"])
	{
		insertVisit();	
	}
}

else
{
	echo "Request method is bad, you should feel bad";
	die();
}

// works
function getPerson($id=0)
{
	global $connection;
	$peopleSql = "SELECT * FROM People";
	
	if($id != 0)
	{
		$peopleSql = "SELECT p.firstname, s.statename, p.food
					FROM Visits v
					INNER JOIN People p ON v.p_id = p.id
					INNER JOIN States s ON v.s_id = s.id
					WHERE v.p_id =" . $id;

		$peopleQuery = mysqli_query ($connection, $peopleSql) or die(mysqli_error($connection));
		$row = mysqli_fetch_array($peopleQuery);
			
		$firstName = $row["firstname"];
		$stateName = $row["statename"];
		$foodName = $row["food"];

			if(!empty($firstName) && !empty($stateName) && !empty($foodName))
			{
				echo "<br><br><center> The human you select is : " . $firstName . "</center>";

				echo "<br><center> The state they're visited : " . $stateName . "</center>";

				while($row = mysqli_fetch_array($peopleSql))
				{
					$stateName = $row["statename"];
					echo "<br><center> The state they're visited : " . $stateName . "</center>";				
				}

				echo "<br><center> Their favor food is : " . $foodName . "<br><br><br><br></center>";
			}

			else
			{
				echo "<br><center> You need to add a visit </center>";
			}
	}
	
	$response = array();
	$peopleQuery = mysqli_query ($connection, $peopleSql) or die(mysqli_error($connection));
	while($row = mysqli_fetch_array($peopleQuery))
	{
		$response[] = $row;
	}
	header('Content-Type: application/json');
	echo json_encode($response);
}

function getStates()
{
	global $connection;
	$stateSql = "SELECT id, statename FROM States";
	$stateQuery = mysqli_query($connection,$stateSql) or die(mysqli_error($connection));

	$response = array();
	while($row = mysqli_fetch_array($stateQuery))
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

	echo "<br>First name entered: " . $firstNameEnter . "<br>";
	echo "<br>Last name entered: " . $lastNameEnter . "<br>";
	echo "<br>Favorite food: " . $foodEnter . "<br>";
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

	echo "Person entered : " . $personEnter . "<br>";
	echo "State entered : " . $stateEnter . "<br>";
	echo "Date entered : " . $visitEnter . "<br>";
}
$connection->close();
?>

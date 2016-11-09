<?php

var_dump($_GET);
die();
// Instead of doing POST, I'm getting information from form.php
// if just running api.php by itself, it will throw error because input is null
$id = $_GET["personID"];
//var_dump($_GET);
var_dump($id);

$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

$connection = mysqli_connect($host, $user, $password);
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}

$connection->select_db($database);
$resultSql =	"SELECT p.firstname, s.statename, p.food
				FROM Visits v
				INNER JOIN People p ON v.p_id = p.id
				INNER JOIN States s ON v.s_id = s.id
				WHERE v.p_id =" . $id;

$query = mysqli_query ($connection, $resultSql) or die(mysqli_error($connection));
$row2 = mysqli_fetch_array($query);
	
$firstName = $row2["firstname"];
$stateName = $row2["statename"];
$foodName = $row2["food"];

// Display Json format here

	if(!empty($firstName) && !empty($stateName) && !empty($foodName))
	{
		echo "<br><br><center> The human you select is : " . $firstName . "</center>";

		echo "<br><center> The state they're visited : " . $stateName . "</center>";

		while($row3 = mysqli_fetch_array($query))
		{
			$stateName = $row3["statename"];
			echo "<br><center> The state they're visited : " . $stateName . "</center>";				
		}

		echo "<br><center> Their favor food is : " . $foodName . "<br><br><br><br></center>";
	}

	else
	{
		echo "<br><center> You need to add a visit </center>";
	}
?>

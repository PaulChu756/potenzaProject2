<?php
// Define variables. 
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

//Create connection
$connection = new mysqli($host, $user, $password);

// Check connection
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
else{
	echo "Connection successfully";
}

// Drop database
$dropDB = "DROP DATABASE myDB";

// Check drop database
if($connection->query($dropDB) === TRUE){
	 echo "<br>Database myDB was successfully dropped";
} else {
    echo "<br>Error dropping database: " . $connection->error;
}

//Create Database called "myDB"
$db = "CREATE DATABASE IF NOT EXISTS myDB";

//Check Datebase
if($connection->query($db) === TRUE){
	echo "<br>Database created successfully";
} else {
	echo "<br>Error creating database: " . $connection->error;
}

// Select Database
$connection->select_db($database);

//Create States Table
$statesTable = "CREATE TABLE IF NOT EXISTS States
(
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
stateabb varchar(2) NOT NULL,
statename varchar(40) NOT NULL
)";

// Create People Table
$peopleTable = "CREATE TABLE IF NOT EXISTS People
(
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
firstname varchar(40) NOT NULL,
lastname varchar(40) NOT NULL,
food varchar(40) NOT NULL
)";

// Create Visit Table
$visitTable = "CREATE TABLE IF NOT EXISTS Visits
(
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
p_id INT(40) NOT NULL,
s_id INT(40) NOT NULL,
FOREIGN KEY (p_id) REFERENCES People(id),
FOREIGN KEY (s_id) REFERENCES States(id),
date_visited varchar(40) NOT NULL
)";

//Check States Table
if($connection->query($statesTable) === TRUE) 
{
	echo "<br>States Table created successfully";
}
else
{
	echo "<br> States Table wasn't created" . $connection->error;
}

//Check People Table
if($connection->query($peopleTable) === TRUE) 
{
	echo "<br> People Table created successfully";
}
else
{
	echo "<br> People Table wasn't created" . $connection->error;
}

//Check Visit Table
if($connection->query($visitTable) === TRUE) 
{
	echo "<br> Visit Table created successfully";
}
else
{
	echo "<br> Visit Table wasn't created" . $connection->error;
}

// Insert data into states table
$stateData = "INSERT INTO States (stateabb, statename) 
VALUES ('LA', 'Louisiana');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('FL', 'Florida');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('TX', 'Texas');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('NM', 'New Mexico');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('ID', 'Idaho');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('IA', 'Iowa');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('ME', 'Maine');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('NV', 'Nevada');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('NY', 'New York');";
$stateData .= "INSERT INTO States (stateabb, statename) 
VALUES ('UT', 'Utah');";

// Check Data in table
if($connection->multi_query($stateData) === TRUE)
{
	$lastID = $connection->insert_id;
	echo "<br>New data create successfully. Last inserted ID is: " . $lastID;
}
else
{
	echo "<br>Error: " . $connection->error;
}

//Close Connection
$connection->close();
?>

<!--Return button-->
<form action = "form.php" method = "get">
<input type = "submit" value = "Return back to form" style = "float: right;"/>

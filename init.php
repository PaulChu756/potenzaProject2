<?php
// Define variables. 
$host = "localhost";
$user = "root";
$password = "root";
$database = "myDB";

//Create connection
$connection = mysqli_connect($host, $user, $password);

// Check connection
if(!$connection){
die("Could not connect: " . mysqli_connect_error());}
else{
	echo "Connection successfully \n";
}

// Drop database
$dropDB = "DROP DATABASE myDB";

// Check drop database
if($connection->query($dropDB) === TRUE){
	 echo "Database myDB was successfully dropped \n";
} else {
    echo "Error dropping database: \n" . $connection->error;
}

//Create Database called "myDB"
$db = "CREATE DATABASE IF NOT EXISTS myDB";

//Check Datebase
if($connection->query($db) === TRUE){
	echo "Database created successfully \n";
} else {
	echo "Error creating database: \n" . $connection->error;
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
	echo "States Table created successfully \n";
}
else
{
	echo "States Table wasn't created \n" . $connection->error;
}

//Check People Table
if($connection->query($peopleTable) === TRUE) 
{
	echo "People Table created successfully \n";
}
else
{
	echo "People Table wasn't created \n" . $connection->error;
}

//Check Visit Table
if($connection->query($visitTable) === TRUE) 
{
	echo "Visit Table created successfully \n";
}
else
{
	echo "Visit Table wasn't created \n" . $connection->error;
}

// Insert data into states table
$insertData = "	INSERT INTO States (stateabb, statename) 
				VALUES ('LA', 'Louisiana');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('FL', 'Florida');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('TX', 'Texas');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('NM', 'New Mexico');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('ID', 'Idaho');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('IA', 'Iowa');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('ME', 'Maine');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('NV', 'Nevada');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('NY', 'New York');";
$insertData .= "INSERT INTO States (stateabb, statename) 
				VALUES ('UT', 'Utah');";

// Insert data into people table
$insertData .= "INSERT INTO People (firstname, lastname, food) 
				VALUES ('Paul', 'Chu', 'Rice');";
$insertData .= "INSERT INTO People (firstname, lastname, food) 
				VALUES ('Chui', 'Chu', 'Steak');";
$insertData .= "INSERT INTO People (firstname, lastname, food) 
				VALUES ('Pandalord', 'Chu', 'Cookies');";
$insertData .= "INSERT INTO People (firstname, lastname, food) 
				VALUES ('LordBabyPanda', 'Chu', 'Milk');";

// Insert data into Visits table
$insertData .= "INSERT INTO Visits (p_id, s_id, date_visited) 
				VALUES ('1', '1', '1994/07/14');";
$insertData .= "INSERT INTO Visits (p_id, s_id, date_visited) 
				VALUES ('1', '2', '1994/07/14');";
$insertData .= "INSERT INTO Visits (p_id, s_id, date_visited) 
				VALUES ('2', '10', '1994/07/14');";
$insertData .= "INSERT INTO Visits (p_id, s_id, date_visited) 
				VALUES ('3', '9', '1994/07/14');";
$insertData .= "INSERT INTO Visits (p_id, s_id, date_visited) 
				VALUES ('4', '7', '1994/07/14');";

// Check stateData in table
if($connection->multi_query($insertData) === TRUE)
{
	$lastID = $connection->insert_id;
	echo "insertData create successfully. Last inserted ID is: \n" . $lastID;
}

else
{
	echo "Error: \n" . $connection->error;
}

//Close Connection
$connection->close();
?>
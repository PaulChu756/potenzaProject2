<!DOCTYPE html>
<html>
  <head>
    <title>Project 1 with BootStrap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8"/>
	<link href="css/bootstrap.css" rel="stylesheet">
	<script src="jquery.js"></script>
	<script src="js/bootstrap.js"></script>
	<script src="drive.js"></script>
  </head>
  
	<body>
		<div class = "container">
			<br><center><img class = "img-responsive" src = "stormtrooper.jpg" alt = "Stormtrooper" width = "200" height="200"></center>
			<center><h1>Follow ze steps </h1></center>
			<p class = "text-center">
			<br>Step 1: Must initialize init.php, cli php init.php.
			<br>Step 2: Add a person to the Database
			<br>Step 3: Add a visit to a person
			<br>Step 4: Select a human!
			</p>	
		</div>
	</body>
</html>

<!--Add Person -->
<form>
<input type ="submit" class = "btn btn-warning" value = "Add a Person" style = "float: right;"/>
</form>

<!--Add Visit -->
<form>
<br><br>
<input type = "submit" class = "btn btn-success" value = "Add a Visit" style = "float: right;"/>
</form>

<center>
<form>
	<br><br>Select a human and learn where they're from and favor food
	<br><br><select name="Name" id="Name">
	<option value="">Select a human</option>
</center>
</form>
<div id = "form"><center><br><strong>Selected person info will be here</strong></center></div>
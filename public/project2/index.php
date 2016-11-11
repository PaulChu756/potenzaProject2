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
	<button type="button" class="btn btn-warning" style="float: right;" data-toggle="collapse" data-target="#person">Add Person</button>
		<div id="person" class="collapse">
			<br><br><center>Add a person to the table<br>
			<span class="error">* required field. </span><br><br>
			First Name: <input type = "text" name = "firstName">
			<span class = "error">* <?php echo $firstNameError;?></span><br><br>
			Last Name: <input type = "text" name = "lastName">
			<span class = "error">* <?php echo $lastNameError;?></span><br><br>
			Favorite Food: <input type = "text" name = "food">
			<span class = "error">* <?php echo $foodError;?></span><br><br>
			<input type = "submit" value = "Submit" class = "btn btn-success"/>
			</center>
		</div>
</form>

<!--Add Visit -->
<form>
<br><br>
<button type="button" class="btn btn-success" style="float: right;" data-toggle="collapse" data-target="#visit">Add Visit</button>
<div id="visit" class="collapse">
<p class="text-right">
<br><br><br>Add a visit
</p>
</div>
</form>

<center>
<form>
	<br><br>Select a human and learn where they're from and favor food
	<br><br><select name="Name" id="Name">
	<option value="">Select a human</option>
</center>
</form>
<div id = "form"><center><br><strong>Selected person info will be here</strong></center></div>
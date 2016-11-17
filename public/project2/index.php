<!DOCTYPE html>
<html>
  <head>
    <title>Project 1 with BootStrap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8"/>
	<!--Bootstrap CDN method-->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
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
<form id="personForm" method="POST">
	<button type="button" class="btn btn-warning" style="float: right;" data-toggle="modal" data-target="#person">Add Person</button>
		<div class="modal fade" id="person" tabindex="-1" role="dialog" aria-labelledby="personLabel">
			<div class="modal-dialog" role="document">
				<div class = "modal-content">
					<div class="modal-header"> 
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class = "modal-title">Add a Person</h4>
					</div>
					<div class = "modal-body">
						First Name: 	<input type = "text" name = "firstName"><br><br>
						Last Name: 		<input type = "text" name = "lastName"><br><br>
						Favorite Food: 	<input type = "text" name = "food"><br><br>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success">Submit</button>
					</div>
				</div>
			</div>
		</div>
</form>

<!--Add Visit -->
<form>
<br><br>
<button type="button" class="btn btn-success" style="float: right;" data-toggle="collapse" data-target="#visit">Add Visit</button>
	<div id="visit" class="collapse">
		<center>
		<!--Select human-->
		<br><br>Select a human
		<br><select name="humanName" id="humanName" class="btn btn-primary dropdown-toggle"></select>

		<!--Select States-->
		<br><br><br><br><br><br>Select a state 
		<br><select name="stateName" id="stateName" class="btn btn-info dropdown-toggle"></select>
		
		<!--Add visit-->
		<br><br><br><br><br><br><br><br><br><br><br>Add a visit to the table
		<br><span class="error">* required field. </span><br><br>
		Date Visited:<br>
		Format: YYYY/MM/DD<br>
		Example: 1994/07/14<br>
		<input type = "text" name = "visit">
		<span class = "error">* <?php echo $visitError;?></span><br><br>
		<input type = "submit" class = "btn btn-success" value = "Enter ze data"/>
		<br><br>
		</center>
	</div>
</form>

<!-- Select a human and output info -->
<center>
<form>
	<br><br>Select a human and learn where they're from and favor food
	<br><br><select name="Name" id="Name">
	<option value="">Select a human</option>
</center>
</form>
<div id = "form"><center><br><strong>Selected person info will be here</strong></center></div>

<!--Jquery CDN method-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<!--BootStrap javascript CDN method>-->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="drive.js"></script>
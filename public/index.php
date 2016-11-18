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
<button id="addPersonButton" type="button" class="btn btn-warning" style="float: right;" data-toggle="modal" data-target="#person">Add Person</button><br><br>
	<div class="modal fade" id="person" tabindex="-1" role="dialog" aria-labelledby="personLabel">
		<div class="modal-dialog" role="document">
			<div class = "modal-content">
			
				<div class="modal-header"> 
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class = "modal-title">Add a Person</h4>
				</div>

				<div class = "modal-body">

					<form method="post" id="personForm">
					First Name: 	<input type="text" id="firstName" name="firstName"><br><br>
					Last Name: 		<input type="text" id="lastName" name="lastName"><br><br>
					Favorite Food: 	<input type="text" id="favoriteFood" name="favoriteFood"><br><br>
					<button type="submit" id="addPersonSubmit" name="addPersonSubmit" class="btn btn-success">Submit</button>

					</form>
				</div>

			</div>
		</div>
	</div>

<!--Add Visit -->
<button id="addVisitButton" type="button" class="btn btn-success" style="float: right;" data-toggle="modal" data-target="#visit">Add Visit</button>
	<div class="modal fade" id="visit" tabindex="-1" role="dialog" aria-labelledby="visitLabel">
		<div class="modal-dialog" role="document">
			<div class = "modal-content">

				<div class="modal-header"> 
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class = "modal-title">Add a Visit</h4>
				</div>

				<div class = "modal-body">
					<form id="visitForm" method="POST"><br><br>
					<!--Select human-->
					<br><br>
					<br><select id="humanNameDropDown" name="humanNameDropDown" class="btn btn-primary dropdown-toggle"></select>
					<!--Select States-->
					<br><br><br><br><br><br>
					<br><select id="stateNameDropDown" name="stateNameDropDown" class="btn btn-info dropdown-toggle"></select>
					<!--Add visit-->
					<br><br><br><br><br><br><br><br><br><br><br> Add a visit to the table <br><br><br>
					Date Visited:<br>
					Format: YYYY/MM/DD<br>
					Example: 1994/07/14<br>
					<input type = "text" name = "visit"><br><br>
					<button type="submit" id="addVisitSubmit" name="addVisitSubmit" class="btn btn-success">Submit</button>
					</form>
				</div>

			</div>
		</div>
	</div>

<!-- Select a human and output info -->
<center>
<form>
	<br><br>Select a human and learn where they're from and favor food <br><br>
	<div id="displayInfo">Selected person info will be here</div>
	<br><br><select name="SelectHumanDropDown" id="SelectHumanDropDown">
	<option>Select a human</option>	
</center>
</form>


<!--Jquery CDN method-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<!--BootStrap javascript CDN method>-->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<!-- Ajax file -->
<script src="drive.js"></script>
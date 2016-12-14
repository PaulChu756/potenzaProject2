// populate people/states, also person/visit form submit
$(document).ready(function(){
	populatePeople();
	populateStates();
	displayData();

	$('#addPersonSubmit').click(function(e){
		e.preventDefault();
		var checkFirstName = $.trim($('#firstName').val());
		var checkLastName = $.trim($('#lastName').val());
		var checkFavoriteFood = $.trim($('#favoriteFood').val());
		if(checkFirstName === '' || checkLastName === '' || checkFavoriteFood === '')
		{
			alert("Please fill out all input fields");
		}
		else
		{
			addPerson();
		}
	});

	$('#addVisitSubmit').click(function(e){
		e.preventDefault();
		var checkVisit = $.trim($('#dateVisit').val());
		if(checkVisit === '')
		{
			alert("Please fill out all input fields");
		}
		else
		{
			addVisit();
		}
	});
});

//display selected person
function displayData()
{
	$("#SelectHumanDropDown").change(function(){
		$.ajax({
			type: "GET",
			url: "api/visits",
			dataType: "json",
			success: function(data)
			{
				var dataLength = data.length;
				var i = $("#SelectHumanDropDown").val();
				$("#displayInfo").empty();

				if(data[i] === undefined)
				{
					alert ("You need to add a visit");
				}
				else
				{
					var firstName = data[i]["firstname"];
					var lastName = data[i]["lastname"];
					var food = data[i]["food"];
					var stateName = data[i]["statename"];
					var dateVisit = data[i]["date_visited"];

					$("#displayInfo").append(
					"First name: " + firstName +
					"<br> Last name: " + lastName +
					"<br> Favorite food: " + food +
					"<br> Visited the State : " + stateName + " on " + dateVisit);
				}
			}
		});
	});
}

//populate people's dropdowns
function populatePeople()
{
	$.ajax({
		type:"GET",
		url:"api/people",
		dataType:"json",
		success : function(data)
		{
			$("#SelectHumanDropDown option").not("#personOptions").remove();
			$("#humanNameDropDown option").not("#personOptions").remove();

			var len = data.length;
			for(var i = 0; i < len; i++)
			{
				var id = data[i]["id"];
				var firstname = data[i]["firstname"];
				$("#SelectHumanDropDown").append("<option value='" + id + "'>" + firstname + "</option>");
				$("#humanNameDropDown").append("<option value='" + id + "'>" + firstname + "</option>");
			}
		},
		error : function(data)
		{
			console.log('failed');
			console.log(data);
		}
	});
}

//populate state dropdown
function populateStates()
{
	$.ajax({
		type:"GET",
		url:"api/states",
		dataType:"json",
		success : function(data)
		{
			var len = data.length;
			for(var i = 0; i < len; i++)
			{
				var id = data[i]["id"];
				var stateName = data[i]["statename"];
				$("#stateNameDropDown").append("<option value='" + id + "'>" + stateName + "</option>");
			}
		}
	});
}

//Add person to database
function addPerson()
{
	$.ajax({
		type: "POST",
		url: "api/people",
		data: $("#personForm").serialize(),
		dataType: "json",
		 success: function(data)
		{
			console.log(data);
			console.log($("#personForm").serialize());
			alert("You have added a person");
			populatePeople();
			displayData();
		}
	});
}

//Add visit to database
function addVisit()
{
	$.ajax({
		type: "POST",
		url: "api/visits",
		data: $("#visitForm").serialize(),
		dataType: "json",
		success: function(data)
		{
			console.log(data);
			console.log($("#visitForm").serialize());
			alert("You have added a visit");
			//populatePeople();
			//displayData();
		}
	});
}

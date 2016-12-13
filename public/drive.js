// populate people/states, also person/visit form submit
$(document).ready(function(){
	populatePeople();
	populateStates();
	displayData();

	$('#addPersonSubmit').click(function(e){
		e.preventDefault();
		addPerson();
	});

	$('#addVisitSubmit').click(function(e){
		e.preventDefault();
		addVisit();
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
				var i = $("#SelectHumanDropDown").val();
				$("#displayInfo").empty();

				var firstName = data[i]["firstname"];
				var lastName = data[i]["lastname"];
				var food = data[i]["food"];
				var stateAbb = data[i]["stateabb"];
				var stateName = data[i]["statename"];
				var dateVisit = data[i]["date_visited"];

				$("#displayInfo").append(
				"First name: " + firstName +
				"<br> Last name: " + lastName +
				"<br> Favorite food: " + food +
				"<br> Visited : " + stateAbb + " " + stateName +
				"<br> on " + dateVisit);
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
			//console.log('success');
			//console.log(data);
			$("#SelectHumanDropDown").empty();
			$("#humanNameDropDown").empty();
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
			//console.log('success');
			//console.log(data);
			$("#stateNameDropDown").empty();
			var len = data.length;
			for(var i = 0; i < len; i++)
			{
				var id = data[i]["id"];
				var stateName = data[i]["statename"];
				$("#stateNameDropDown").append("<option value='" + id + "'>" + stateName + "</option>");
			}
		},
		error : function(data)
		{
			console.log('failed');
			console.log(data);
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
			populatePeople();
			displayData();
		}
	});
}

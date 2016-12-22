// populate people/states, also person/visit form submit
$(document).ready(function(){
	populatePeople();
	populateStates();
	displayData();
	$("#personForm")[0].reset();
	$("#visitForm")[0].reset();

	$('#addPersonSubmit').click(function(e){
		e.preventDefault();
			addPerson();
			$("#personForm")[0].reset();
	});

	$('#addVisitSubmit').click(function(e){
		e.preventDefault();
			addVisit();
			$("#visitForm")[0].reset();
	});
});

//display selected person
function displayData()
{
	$("#SelectHumanDropDown").change(function(){
		var selectPerson = $("#SelectHumanDropDown").val();
		$.ajax({
			type: "GET",
			url: "api/visits/" + selectPerson,
			dataType: "json",
			success: function(data)
			{
				$("#displayPeopleInfo").empty();
				$("#displayVisitInfo").empty();
				var dataLength = data.length;
				console.log(dataLength);

				if(dataLength > 0)
				{
					var firstName = data[0]["firstname"];
					var lastName = data[0]["lastname"];
					var food = data[0]["food"];

					$("#displayPeopleInfo").append(
					"First name: " + firstName +
					"<br> Last name: " + lastName +
					"<br> Favorite food: " + food);

					for(var i = 0; i < dataLength; i++)
					{
						var stateName = data[i]["statename"];
						var dateVisit = data[i]["date_visited"];

						$("#displayVisitInfo").append("Visited the State : " + stateName + " on " + dateVisit + "<br>");
					}
				}
				else
				{
					$.ajax({
						type:"GET",
						url: "api/people/" + selectPerson,
						dataType: "json",
						success: function(data)
						{
							$("#displayPeopleInfo").empty();
							$("#displayVisitInfo").empty();

							var firstName = data[0]["firstname"];
							var lastName = data[0]["lastname"];
							var food = data[0]["food"];

							$("#displayPeopleInfo").append(
							"First name: " + firstName +
							"<br> Last name: " + lastName +
							"<br> Favorite food: " + food +
							"<br> has never travled in their life.");
						}
					});
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
		},
		error: function(data, status, xhr)
		{
			alert("Error: Please fill out all inputs");
			console.log(data);
			console.log(status);
			console.log(xhr);
			console.log($("#personForm").serialize());
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
		},
		error: function(data, status, xhr)
		{
			alert("Error: Please fill out all inputs");
			console.log(data);
			console.log(status);
			console.log(xhr);
			console.log($("#personForm").serialize());
		}
	});
}

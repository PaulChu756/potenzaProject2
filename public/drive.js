// populate people/states, also person/visit form submit
$(document).ready(function(){
	populatePeople();
	populateStates();
	displayData();

	$('#personForm').submit(function(e){
		e.preventDefault();
		addPerson();
	});

	$('#visitForm').submit(function(e){
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
			url: "api/people",
			dataType: "json",
			success: function(data)
			{
				$("#displayInfo").empty();
				var i = $("#SelectHumanDropDown").val();
				var firstname = data[i-1]["firstname"];
				var lastname = data[i-1]["lastname"];
				var food = data[i-1]["food"];
				$("#displayInfo").append(
				"First name: " + firstname + 
				"<br> Last name: " + lastname + 
				"<br> Favorite food: " + food);
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
			var len = data.length;
			for(var i = 0; i < len; i++)
			{
				var id = data[i]["id"];
				var firstname = data[i]["statename"];
				$("#stateNameDropDown").append("<option value='" + id + "'>" + firstname + "</option>");
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
		url: "api/people", // api/people
		data: $("#personForm").serialize(),
		 success: function(data,status,xhr) 
		{
			console.log(data);
			console.log(status);
			console.log(xhr);
			console.log($("#personForm").serialize());
			console.log("You have added a person");
			populatePeople();
			displayData();
		},
		 error: function(data,status,xhr) 
		{
			console.log(data);
			console.log(status);
			console.log(xhr);
			console.log($("#personForm").serialize());
			console.log("error");
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
		url: "api/visits", // api/visits
		data: $("#humanNameDropDown, #stateNameDropDown, #visitForm").serialize(),
		success: function(data)
		{
			console.log(data);
			console.log($("#visitForm").serialize());
			console.log("You have added a visit");
		},
		error: function(data)
		{
			console.log(data);
			console.log($("#visitForm").serialize());
		}
	});
}
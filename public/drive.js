//display selected person
$("#SelectHumanDropDown").change(function(){
	$.ajax({
		type: "GET",
		url: "api.php",
		dataType: "json",
		success: function(data)
		{
			$("#displayInfo").empty();
			var i = $("#SelectHumanDropDown").val();
			var firstname = data[i-1]["firstname"];
			var lastname = data[i-1]["lastname"];
			var food = data[i-1]["food"];
			$("#displayInfo").append("First name: " + firstname + "<br> Last name: " + lastname + "<br> Favorite food: " + food);
		}
	});
});

//exe functions
$(document).ready(function(){
	populatePeople(); // done but static
	//populateStates(); // done but static
});

//populate people's dropdowns
function populatePeople()
{
	$.ajax({
		type:"GET",
		url:"api.php",
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
		url:"api.php",
		dataType:"json",
		success : function(data)
		{
			console.log('success');
			console.log(data);
			var len = data.length;
			for(var i = 0; i < len; i++)
			{
				var id = data[i]["id"];
				var firstname = data[i]["statename"];
				$("#stateName").append("<option value='" + id + "'>" + firstname + "</option>");
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
$(document).ready(function(){
	$("#addPersonSubmit").click(function(){
		$.ajax({
			type: "POST",
			url: "api.php",
			data: $("#personForm").serialize(),
			success: function(data)
			{
				console.log(data);
				console.log($("#personForm").serialize());
				alert("You have added a person");
			}
		});
	});
});

//Add visit to database
$(document).ready(function(){
	$("#addVisitSubmit").click(function(){
		$.ajax({
			type: "POST",
			url: "api.php",
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
				console.log("Error");
			}
		});
	});
});

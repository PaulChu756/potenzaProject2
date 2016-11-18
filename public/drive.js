//Add person to database
$(document).ready(function(){
	$("#addPersonSubmit").click(function(){
		$.ajax({
			type: "POST",
			url: "api.php",
			data: $("#personForm").serialize(),
			success: function()
			{
				console.log($("#personForm").serialize());
				console.log("You have added a person");
			},
			error: function()
			{
				console.log($("#personForm").serialize());
				console.log("Error");
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
			success: function()
			{
				console.log($("#visitForm").serialize());
				console.log("You have added a visit");
			},
			error: function()
			{
				console.log($("#visitForm").serialize());
				console.log("Error");
			}

		});
	});
});

$(document).ready(function(){
	populatePeople();
	//populateStates();
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
			console.log(data);
			var len = data.length;
			for(var i = 0; i < len; i++)
			{
				var id = data[i]["id"];
				var firstname = data[i]["firstname"];
				$("#Name").append("<option value='" + id + "'>" + firstname + "</option>");
				$("#humanName").append("<option value='" + id + "'>" + firstname + "</option>");
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

//Comment section
/*
beforeSend : function() 
		{
			console.log('sending now...');
		},
*/
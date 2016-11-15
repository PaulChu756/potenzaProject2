$(document).ready(function(){
	console.log("test");
	//populatePeople();
	$.ajax({
		type:"GET",
		url:"api.php",
		dataType:"json",
		beforeSend : function() {
			console.log('sending now...');
		},
		success: function(data)
		{
			console.log(data);
			//options.append($("<option value='" + result[i].id + "'>" + result[i].firstname + "</option>"));
		},
		fail: function(data)
		{
			console.log('test');
		},
		always: function(data)
		{
			console.log('no matter what');
		}
	});
});

function populatePeople()
{
	$.getJSON("api.php", function(data)
	{
		// stops here
		// none fire
		console.log("fire please")
		console.log(data);
		/*
		var options = $("#Name");
		$.each(data, function()
		{
			options.append($("<option value='" + result[i].id + "'>" + result[i].firstname + "</option>"));
		});
		*/
	});
}

/*
$.ajax({
		type:"GET",
		url:"api.php",
		dataType:"json",
		success: function(data)
		{
			console.log(data);
			options.append($("<option value='" + result[i].id + "'>" + result[i].firstname + "</option>"));
		},
		fail:,
		always:
	});
*/

function selectPeople()
{
	var getInfo = $(this).val();
		$.ajax({
			type:"GET",
			url:"api.php",
			data:{personID:getInfo},
			dataType:"json",
			success: function(json)
			{
				var name = $("#Name");
				$name.empty();
				$name.append($("<option></option>")
					.attr("value","").text("Select a Human"));
				$.each(json, function(value,key)
				{
					$name.append($("<option></option>")
						.attr("value", value).text(key));
				});
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				console.log("didn't go through");
			},
	});
}

function selectStates()
{
    var getInfo = $(this).val();
		$.ajax({
			type:"GET",
			url:"api.php",
			data:{personID:getInfo},
			dataType:"json",
			success: function(json)
			{
				var name = $("#Name");
				$name.empty();
				$name.append($("<option></option>")
					.attr("value","").text("Select a Human"));
				$.each(json, function(value,key)
				{
					$name.append($("<option></option>")
						.attr("value", value).text(key));
				});
			}
	});
}
$(document).ready(function(){
	populatePeople();
});

function populatePeople()
{
	console.log("test");
	$.ajax({
		type:"GET",
		url:"api.php",
		dataType:"json",
		success : function(data)
		{
			var id = data[id];
			var firstname = data[firstname];
			$.each()
			{
				
			}
			//options.append($("<option value='" + result[i].id + "'>" + result[i].firstname + "</option>"));
		},
	});
}

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

//Comment section
/*
beforeSend : function() 
		{
			console.log('sending now...');
		},
*/
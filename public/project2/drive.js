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
			console.log('sucxcess');
			console.log(data);
			/*
			var len = data.length;
			for(var i = 0; i < len; i++)
			{
				var id = data[i]["id"];
				var firstname = data[i]["firstname"];
				$("#Name").append("<option value='" + id + "'>" + firstname + "</option>");
			}
			*/
		},
		error : function(data)
		{
			console.log('fadiled');
			console.log(data);
		}
	});
}

/*
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
*/
//Comment section
/*
beforeSend : function() 
		{
			console.log('sending now...');
		},
*/
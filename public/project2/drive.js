$(document).ready(function(){
	console.log("test");
	populatePeople();
});

function populatePeople()
{
		$.ajax({
		type:"GET",
		url:"api.php",
		dataType:"json",
		beforeSend : function() 
		{
			console.log('sending now...');
		},
		success: function(data)
		{
			alert("success");
			console.log(data);
			//options.append($("<option value='" + result[i].id + "'>" + result[i].firstname + "</option>"));
		},
		fail: function(data)
		{
			alert("fail");
			console.log('fail');
		},
		always: function(data)
		{
			console.log('no matter what');
		}
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
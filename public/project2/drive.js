$(document).ready(function(){
	console.log("ready");
	$('#Name').change(function(){
	populatePeople();
	});
});

function populatePeople()
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

function populateStates()
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
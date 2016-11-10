<script src="jquery.js"></script>

function populatePeople()
{
	$('#Name').change(function(){
	var getInfo = $(this).val();
		$.ajax({
			type:"GET",
			url:"api.php",
			data:{personID:getInfo},
			dataType:"json",
			success: function(json)
			{
				$('#Name').empty();
				$('#Name').append("<option='0'>Select a Human</option>");
				$.each(json, function(value,key)
				{
					$('$Name').append("<option></option>")
				}
			}
		})
	})
}

$(document).ready(function(){
	$('#Name').change(function(){
		var getInfo = $(this).val();
		$.ajax({
				type: "GET",
				data:{personID:getInfo},
				url: "api.php",
				dataType:'json',
				success: function(json) {
					var $el = $("#Name");
					$el.empty();
					$el.append($("<option></option>")
						.attr("value", '').text('Select a human'));
					$.each(json, function(value,key){
						$el.append($("<option></option>")
							.attr("value", value).text(key));
					});
				}
		});
	});
});
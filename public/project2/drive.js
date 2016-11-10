<script src="jquery.js"></script>

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
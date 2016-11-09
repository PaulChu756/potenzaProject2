<script src="jquery.js"></script>

$(document).ready(function(){
	$('#Name').change(function(){
		var getInfo = $(this).val();
		$.ajax({
				type: "POST",
				url: "api.php",
				// Key | Value 
				data:{personID:getInfo},
				success: function(data)
				{
					$('#showPerson').html(data);
				}
				// type of data we expect back
				//dataType : "json",
		});
	});
});
<script src="jquery.js"></script>
console.log("cookies");
die();

$(document).ready(function(){
	$('#Name').change(function(){
		var getInfo = $(this).val();
		$.ajax({
				url: "api.php",
				type: "POST",
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

/*		
Pure javascript, doesn't use Jquery at all
function getInfo(str)
{
	if(str == "")
	{
		return document.getElementById("form").innerHTML = "";
	}
	else 
	{
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function()
		{
			// readyState 4 means complete && 200 means complete
			if(this.readyState == 4 && this.status == 200) 
			{
				document.getElementById("form").innerHTML = this.responseText;
			}
		};
		xmlhttp.open("GET","api.php?q="+str, true);
		xmlhttp.send();
	}
}
*/
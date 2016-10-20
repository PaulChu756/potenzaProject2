
<script>

//call back function
function callback(url, loadFunction)
{
	var xmlhttp = new XMLHttpRequest();

        xmlhttp.onreadystatechange = function()
	{
		if(this.readyState == 4 && this.status == 200) 
		{
			loadFunction(this);
		}
	};
	
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
}

function getAllStates(){}

function getAllPeople(){}


</script>


<script>
function callBack(url, cFunction)
{
	var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function()
	{
		// readyState 4 request finished and response is ready 
		// Status 200 is ok
		if(this.readyState == 4 && this.status == 200) 
		{
			console.log("Got Info");
			cFunction(this;)
			//document.getElementById("demo").innerHTML = this.responseText;
		}
	};
	
	xhttp.open("GET", "form.php", true);
	xhttp.send();
}

function getAllStates(xhttp)
{

}

function getAllPeople(xhttp)
{
	document.getElementById("form").innerHTML = xhttp.responseText;
}
</script>

<!DOCTYPE html>
<html>
<head>
<title>Print Dive</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/plannerstyle.css">
<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed' rel='stylesheet' type='text/css'>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="js/diveChart.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script>
function printProfile() {
	window.print();
}

$(document).ready(
	function () {
		drawChart();
})
</script>
</head>
<body>

<div id="container" class="container">

	<div style="text-align: right; font-size: 12px"><a id="print" onClick="printProfile()"><span class="glyphicon glyphicon-print"></span> Print profile</div></a>
		<h2> Final Dive Profile </h2>
	<div class="row">
		<div class="col-md-8">
			<div id="chart_div"></div>
		</div>
		<div class="col-md-4">
			<strong>Profile #:</strong> <?php echo $_POST["did"]; ?><br />
			<strong>Date:</strong> <?php echo $_POST["dd"]; ?><br />
			<strong>Dive Buddies:</strong> <?php echo $_POST["db"]; ?><br />
			<strong>Location:</strong> <?php echo $_POST["dl"]; ?><br />
		</div>
	</div>
</div>
<div id="disclaimer">
	<span class="glyphicon glyphicon-warning-sign"></span> WARNING: This is a prototype and CANNOT be used to plan real dives.
</div>
</body>
</html> 
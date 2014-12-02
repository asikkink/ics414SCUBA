
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
function drawChart(){
	var data = google.visualization.arrayToDataTable([
	['Director (Year)',  'Depth', 'Stops'],
	['Alfred Hitchcock (1935)', 55, 15],
	['Ralph Thomas (1959)',     65,15],
	['Don Sharp (1978)',        25,15],
	['Don Sharp (1978)',        5,15],
	['Don Sharp (1978)',        35,15],
	['Don Sharp (1978)',       20,15],
	['James Hawes (2008)',      25,15]
	]);

	var options = {
title: 'The decline of \'The 39 Steps\'',
vAxis: {title: 'Accumulated Rating'},
isStacked: true
	};

	var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_div'));

	chart.draw(data, options);
}

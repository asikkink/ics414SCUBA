
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);

// $('#addDive').on('click', function(){
	// drawChart();
// });



function drawChart(){
	// var data = google.visualization.arrayToDataTable([
	// ['Director (Year)',  'Depth', 'Stops'],
	// ['Alfred Hitchcock (1935)', 55, 15],
	// ['Ralph Thomas (1959)',     65,15],
	// ['Don Sharp (1978)',        25,15],
	// ['Don Sharp (1978)',        5,15],
	// ['Don Sharp (1978)',        35,15],
	// ['Don Sharp (1978)',       20,15],
	// ['James Hawes (2008)',      25,15]
	// ]);
	
	//get dive rows
	$.ajax({
		type : 'POST',
		url : 'ajax_handler.php',
		data :{
			action : 'get_dive_data'
		},
success: function(results){
			var myJson = JSON.parse(results);
			var time;
			var depth = 0;
			// console.log(myJson);
			// console.log(myJson[0].depth);
			// console.log(myJson.length);
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Time');
			data.addColumn('number', 'Depth');
			for(var i = 0; i < myJson.length; i++){
				time = (myJson[i].time);
				depth = parseInt(myJson[i].depth);
				data.addRow(
				[time, depth]
				);
				console.log(myJson[i].depth);
				console.log(myJson[i].time);
			}
			var options = {
title: 'Dive Profile',
vAxis: {title: 'Depth of Dive'},
isStacked: true
			};

			var chart = new google.visualization.SteppedAreaChart(document.getElementById('chart_div'));

			chart.draw(data, options);
			
			
		},
error: function(){
			console.log('failure');
		}
		
		
	})
	

	
}

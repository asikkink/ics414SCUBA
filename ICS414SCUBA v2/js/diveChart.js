
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);


function drawChart(){
	
	//get dive rows
	$.ajax({
		type : 'POST',
		url : 'ajax_handler.php',
		data :{
			action : 'get_dive_data'
		},
success: function(results){
			var myJson = JSON.parse(results);
			var surfInt;
			var time;
			var depth = 0;
			var postDivePG = "";
			var postSurfIntPG = "";
			// console.log(myJson);
			// console.log(myJson[0].depth);
			// console.log(myJson.length);
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Time');
			data.addColumn('number', 'Depth');
			data.addColumn('number', 'Surface Interval');
			for(var i = 0; i < myJson.length; i++){
				surfInt = myJson[i].surf_int;
				
				time = (myJson[i].time + " /" + surfInt);
				depth = parseInt(myJson[i].depth);
				data.addRow(
				[time, depth, 0]
				);
				// console.log(myJson[i].depth);
				// console.log(myJson[i].time);
			}
			var options = {
			bar: {groupWidth: '50%'},
title: 'Dive Profile',
vAxis: {title: 'Depth of Dive',
	direction: -1
},
hAxis:{
	title: 'Bottom Time and Surface Interval'
},
isStacked: true
			};

			var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

			chart.draw(data, options);
			
			
		},
error: function(){
			console.log('failure');
		}
		
		
	})
	

	
}


google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);


function drawChart(){
	var data = new google.visualization.DataTable();
data.addColumn({ type: 'string', label: 'Labels' });
data.addColumn({ type: 'number', label: 'Bar Series' });
data.addColumn({ type: 'number', label: 'Line Series' });
data.addColumn({ type: 'string', role: 'annotation' });

data.addColumn({ type: 'number', label: 'Bar Series' });
data.addColumn({ type: 'number', label: 'Line Series' });
data.addColumn({ type: 'string', role: 'annotation' });

data.addRows([['Label1', 10, 10, '10', 20, 20, '20'],
             ['Label1', 20, 20, '20',10,10,'10'],
             ['Label1', 40, 40, '40',30,30,'30'],
             ['Label1', 100, 100, '100',10,10,'10'],
             ['Label1', 30, 30, '30',20,20,'20'],
             ]);

var barchart = new google.visualization.ComboChart(document.getElementById('chart_div'));
var options = {series: [{ type: 'bars' },
                        { type: 'line', lineWidth: 0, visibleInLegend:false, pointSize: 0},
						{ type: 'bars' },
                        { type: 'line', lineWidth: 0, visibleInLegend:false, pointSize: 0}],
				vAxis: {title: 'Depth of Dive',
					direction: -1,
					// gridlines:{
						// count: 10
					// }
				},
				bar: {groupWidth: '100%'},
						};

barchart.draw(data, options);
	
	
	
	// //get dive rows
	// $.ajax({
		// type : 'POST',
		// url : 'ajax_handler.php',
		// data :{
			// action : 'get_dive_data'
		// },
// success: function(results){
			// var myJson = JSON.parse(results);
			// var diveNum;//X axis
			// var surfInt;//Red surface
			// var time; //below the depth bar
			// var depth = 0; //the bar
			// var postDivePG = ""; //inside the bar hopefully
			// var postSurfIntPG = "";
			// // console.log(myJson);
			// // console.log(myJson[0].depth);
			// // console.log(myJson.length);
			// var data = new google.visualization.DataTable();
			// data.addColumn('string', 'DiveNumber');
			// data.addColumn('number', 'Depth');
			// data.addColumn('number', 'Surface Interval');
			// for(var i = 0; i < myJson.length; i++){
				// surfInt = myJson[i].surf_int;
				
				// time = (myJson[i].time + " /" + surfInt);
				// depth = parseInt(myJson[i].depth);
				// data.addRow(
				// [time, depth, 5]
				// );
				// // console.log(myJson[i].depth);
				// // console.log(myJson[i].time);
			// }
			// var options = {
			// bar: {groupWidth: '100%'},
// title: 'Dive Profile',
// vAxis: {title: 'Depth of Dive',
	// direction: -1
// },
// hAxis:{
	// title: 'Dive Number'
// },
// //I'm so stupid... god damn. it was set to true this whole time and I missed this line
// //isStacked: false
			// };

			// var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

			// chart.draw(data, options);
			
			
		// },
// error: function(){
			// console.log('failure');
		// }
		
		
	// })
	

	
}

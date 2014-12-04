
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);

//Retrieve dive information from dive chart
//Helpful source: http://stackoverflow.com/questions/10871729/put-labels-on-top-of-inside-bar-in-google-interactive-bar-chart/12918002#12918002
function drawChart(){
	$.ajax({
		type : 'POST',
		url : 'ajax_handler.php',
		data :{
			action : 'get_dive_data'
		},
success: function(results){
			var myJson = JSON.parse(results);
			var diveNum;//X axis
			var surfInt;//Red surface
			var time; //below the depth bar
			var depth = 0; //the bar
			var postDivePG = ""; //inside the bar hopefully
			var postSurfIntPG = "";
			
			//Set up google charts
			var data = new google.visualization.DataTable();
			data.addColumn({ type: 'string', label: 'Labels' });
			data.addColumn({ type: 'number', label: 'Depth' });
			data.addColumn({ type: 'number', label: 'Depth' });
			data.addColumn({ type: 'string', role: 'annotation' });
			//Add tooltips
			data.addColumn({type: 'string', role: 'tooltip'});
			for(var i = 0; i < myJson.length; i++){
				//Initialize variables
				diveNum = myJson[i].dive_num;
				surfInt = myJson[i].surf_int;
				depth = parseInt(myJson[i].depth);
				time = (myJson[i].time);
				postDivePG = myJson[i].post_dive_pg;
				postSurfIntPG = myJson[i].post_surf_int_pg;
				//Add dive row
				data.addRows([
				['Dive '+ diveNum , depth, depth, time +" min", "Post dive pg: "+ postDivePG], //dive 1
				['Rest '+ diveNum, 0, 0, surfInt +" min", "Post surf interval pg: "+ postSurfIntPG], //surface
				]);
				var barchart = new google.visualization.ComboChart(document.getElementById('chart_div'));
				var options = {series: [{ type: 'bars' },
					{ type: 'line', lineWidth: 0, visibleInLegend:false, pointSize: 0}],
					vAxis: {title: 'Depth of Dive',
					direction: -1
					},
					bar: {groupWidth: '100%'},
					tooltip: {isHtml: true}
				};

				barchart.draw(data, options);

			}		
			
		}
	});
	

	
}

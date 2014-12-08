
google.load("visualization", "1", {packages:["corechart"]});
//google.setOnLoadCallback(drawChart);

//Retrieve dive information from dive chart
//Helpful source: http://stackoverflow.com/questions/10871729/put-labels-on-top-of-inside-bar-in-google-interactive-bar-chart/12918002#12918002
function drawChart(profile_id){
	$.ajax({
		type : 'POST',
		url : 'ajax_handler.php',
		data :{
			action : 'get_dive_data',
			profileID : profile_id
		},
		success: function(results){
			var barchart = new google.visualization.ComboChart(document.getElementById('chart_div'));
			console.log(results);
			if (results != 0) {
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
				data.addColumn({type: 'string', role: 'tooltip','p': {'html': true}});
				for(var i = 0; i < myJson.length; i++){
					//Initialize variables
					diveNum = myJson[i].dive_num;
					surfInt = myJson[i].surf_int;
					depth = parseInt(myJson[i].depth);
					time = (myJson[i].time);
					postDivePG = myJson[i].post_dive_pg;
					postSurfIntPG = myJson[i].post_surf_int_pg;
					residual_time = myJson[i].residual_time;

					if (myJson[i].ss_depth == 0) {
						safetyStop = "None";
					}
					else {
						safetyStop = myJson[i].ss_depth + " ft. for "+ myJson[i].ss_time + " min";
					}

					//Add dive row
					data.addRows([
					['Dive '+ diveNum , depth, depth, time +" min", "<b>Pressure Group <br>after Dive:</b> "+ postDivePG], //dive 1
					['Rest '+ diveNum, 0, 0, surfInt +" min", "<b>Pressure Group<br> after Surface Interval:</b> "+ postSurfIntPG+"<br><b>RNT: </b>"+residual_time+
					"<br><b>Safety Stop: </b><br>" + safetyStop], //surface
					]);
					
					var options = {
						series: [{ type: 'bars', visibleInLegend: false },
							{ type: 'line', lineWidth: 0, visibleInLegend:false, pointSize: 0}],
						vAxis: {
							title: 'Depth of Dive',
							direction: -1
						},
						bar: {
							groupWidth: '100%',
							strokeWidth: '1'
							
							},
						tooltip: {isHtml: true},
						//colors: ['blue', 'orange'],
						backgroundColor: '#E4E4E4',
						chartArea: {
							backgroundColor: 'white'
						}
					};

					barchart.draw(data, options);

				}
		}
		else {
			barchart.clearChart();
		}
	}
	});
}

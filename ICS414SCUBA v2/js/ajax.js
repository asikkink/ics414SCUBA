$(document).ready(



function () {

	//ajax called at dom initialization to update depth field with options
	$.ajax({
		type : 'POST',
		url : 'ajax_handler.php',
		data : {
			action : "initialize",
			init : 'true'
		},
		success : function (result) {
			//console.log(result);
			$('#depth_select').html(result);
		}
	});

	
	//ajax function for depth selection to update bottom time field options
	$('#depth_select').change(
	function () {
		value = $('#depth_select').val();
		//alert(value);
		$.ajax({
			type : 'POST',
			url : 'ajax_handler.php',
			data : {
				action : 'select_max_depth',
				depth : value
			},
			success : function (result) {
				//console.log(result);
				$('#bottom_time_select').html(result);

			}
		});
	})

	//ajax function for bottom time selection to update surface int field options
	$('#bottom_time_select').change(
	function () {
		value = $('#bottom_time_select').val();
		depth = $('#depth_select').val();
		//alert(value);
		$.ajax({
			type : 'POST',
			url : 'ajax_handler.php',
			data : {
				action : 'select_bottom_time',
				bottom_time : value,
				depth_selected : depth
			},
			success : function (result) {
				//console.log(result);
				$('#surface_int_select').html(result);

			}
		});

	})
	//ajax function for AddDive selection to update graph and final calculations
	$('#addDiveForm').on('submit',
	function (e) {
		e.preventDefault();
		//debug mode!
		var debug = false;
		if(debug == true){
			var formValues = "action=debugMode&" + $('#addDiveForm').serialize();
			//console.log(formValues);
			$.ajax({
				type : "POST",
				url : "testCases.php",
				data : formValues,
				success : function (data) {
					//$('#surface_int_select').html(result);
					//console.log("hello");
					console.log(data);
				},
				error : function () {
					//console.log("failed");
				}
			});
			
			
		}
		else{
			
			var formValues = "action=addingDive&" + $('#addDiveForm').serialize();
			//console.log(formValues);
			//alert(formValues);
			$.ajax({
				type : "POST",
				url : "ajax_handler.php",
				data : formValues,
				success : function (data) {
					//console.log("hello");
					//console.log(data);
					
					$('#dives').html(data);
					$('#addDive').text('Save Dive');
					drawChart();
					$('input:radio[name=diveRadio]').change(
					function(){
						
						value = $('input:radio[name=diveRadio]:checked').val();
						$.ajax({
							type : 'POST',
							url : 'ajax_handler.php',
							data : {
								action : 'select_dive_to_edit',
								diveNum : value
							},
							dataType: "json",
							success : function (result) {
								//console.log(result['depth'] + " " + result['time'] + " " + result['surf_int']);
								$('#depth_select option:selected').attr("selected",null);
								$('#depth_select option[value=' + result['depth'] + ']').attr("selected", "selected");
								$('#bottom_time_select option:selected').attr("selected", null);
								$('#bottom_time_select option[value=' + result['time'] + ']').attr("selected", "selected");
								$('#surface_int_select option:selected').attr("selected", null);
								$('#surface_int_select option[value=' + result['surf_int'] + ']').attr("selected", "selected");

								$('#addDive').text('Save Dive');
							}

						});
					}
					);
					
					
				},
				error : function () {
					//console.log("failed");
				}
			});
		}

	})
	
	$('#newDive').click(
		function () {
			$.ajax({
				type : 'POST',
				url : 'ajax_handler.php',
				data : {
					action : "refresh",
				},
				success : function (result) {
					//console.log(result);
					$('#depth_select').html(result);
					$('#bottom_time_select').empty();
					$('#surface_int_select').empty();
					$('#addDive').text('Add Dive');
				}
			});
	})
	
})

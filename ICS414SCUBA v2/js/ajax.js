var profileID;

$(document).ready(
	function () {
	
	jQuery.extend({
		getId: function() {
			var id = null;
			//ajax called at dom initialization to update depth field with options
			$.ajax({
				type : 'POST',
				url : 'ajax_handler.php',
				data : {
					action : "initialize",
					init : 'true'
				},
				async : false,  //need to be careful with this
				dataType : "json",
				success : function (result) {
					$('#depth_select').html(result['html']);
					id = result['pid'];
					$('#bottom_time_select').prop("disabled", true);
					$('#surface_int_select').prop("disabled", true);
					$('#addDive').prop("disabled", true);
				},
				error : function (result) {
					console.log("failed");
				}
			});
			return id;
		}
	});

	// GLOBAL VARIABLE: profile ID
	//==========!!!!!!!!!!!!!!
	profileID = $.getId();
	//profileID = 1;
	drawChart(profileID);
	
	//ajax function for depth selection to update bottom time field options
	$('#depth_select').change(
	function () {
		value = $('#depth_select').val();
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
				$('#bottom_time_select').prop("disabled", false);
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
				$('#surface_int_select').prop("disabled", false);
			}
		});

	})


	$('#surface_int_select').change(
		function() {
			$('#addDive').prop("disabled", false);
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
			
			var formValues = "action=addingDive&" + $('#addDiveForm').serialize() + "&pid=" + profileID;
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
					$('#diveNumber').val($('input:radio[name=diveRadio]:checked').val());
	//=====!!!!!!!!!Draw chart
					drawChart(profileID);
					$('input:radio[name=diveRadio]').change(
					function(){
						//variable for depth to call getDepth
						var resultArray;
						
						value = $('input:radio[name=diveRadio]:checked').val();
						$.ajax({
							type : 'POST',
							url : 'ajax_handler.php',
							data : {
								action : 'select_dive_to_edit',
								diveNum : value,
								profileID : profileID
							},
							dataType: "json",
							success : function (result) {
								//will use the ['depth'] 
								/*Ugly ajax code again
								*===============================================
								* Reloads the Bottom Time Field so the correct options are displayed
								*/
								value = result['depth'];
								displayBottomTime(value);
								//Anna added this to reload the surface interval field
								var time = result['time'];
								displaySurfInt(value, time);
								//================================================
								
								//alert(result['depth'] + " " + result['time'] + " " + result['surf_int'] + " " + result['dive_num']);
								$('#depth_select option:selected').attr("selected",null);
								$('#depth_select option[value=' + result['depth'] + ']').attr("selected", "selected");
								$('#bottom_time_select option:selected').attr("selected", null);
								$('#bottom_time_select option[value=' + result['time'] + ']').attr("selected", "selected");
								$('#surface_int_select option:selected').attr("selected", null);
								$('#surface_int_select option[value=' + result['surf_int'] + ']').attr("selected", "selected");
								$('#diveNumber').val(result['dive_num']);

								$('#addDive').text('Save Dive');
							}
						});
					});					
				},
				error : function () {
					console.log("failed");
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
				$('#depth_select').html(result);
				$('#bottom_time_select').empty();
				$('#bottom_time_select').prop("disabled", true);
				$('#surface_int_select').empty();
				$('#surface_int_select').prop("disabled", true);
				$('#diveNumber').val(0);

				$('#addDive').prop("disabled", true);
				$('#addDive').text('Add Dive');
			}
		});
	})

	$("input[name='defSS']").change(
		function() {
			if (this.checked) {
				$('#safety_depth_select').val(15);
				$('#safety_time_select').val(3);
				
				$('#safety_depth_select').change(
					function() {
						$("input[name='defSS']").prop("checked", false);
					}
				)

				$('#safety_time_select').change(
					function() {
						$("input[name='defSS']").prop("checked", false);
					}
				)
			}
	})
	
})


//Organized code... for that long nested ajax code
//This one works
function displayBottomTime(depth){
	$.ajax({
		type : 'POST',
		url : 'ajax_handler.php',
		data : {
			action : 'select_max_depth',
			depth : value
		},
		async: false,
		success : function (result) {
			//console.log(result);
			$('#bottom_time_select').html(result);

		}
	});
}

//similar to displayBottomTime, but for Surface Interval
function displaySurfInt(depth, bottom_time){
$.ajax({
		type : 'POST',
		url : 'ajax_handler.php',
		data : {
			action : 'select_bottom_time',
			depth_selected: depth,
			bottom_time : bottom_time
		},
		async: false,
		success : function (result) {
			$('#surface_int_select').html(result);
		}
	});
}
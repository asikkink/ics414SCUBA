// GLOBAL VARIABLE
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
					// fill in the drop down options for depths
					$('#depth_select').html(result['html']);

					// profile ID
					id = result['pid'];

					$('#bottom_time_select').prop("disabled", true);
					$('#surface_int_select').prop("disabled", true);
					$('#safety_time_select').prop("disabled", true);
					$('#addDive').prop("disabled", true);
					$('#deleteDive').hide();
					$('#finish').prop("disabled", true);
				},
				error : function (result) {
					console.log("failed");
				}
			});
			return id;
		}
	});

	// GLOBAL VARIABLE: profile ID
	profileID = $.getId();
	//Set pid value
	$('#pid').val(profileID);
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
				$('#bottom_time_select').html(result);
				$('#bottom_time_select').prop("disabled", false);
				validation();
			}
		});
	})

	//ajax function for bottom time selection to update surface int field options
	$('#bottom_time_select').change(
	function () {
		value = $('#bottom_time_select').val();
		depth = $('#depth_select').val();
		$.ajax({
			type : 'POST',
			url : 'ajax_handler.php',
			data : {
				action : 'select_bottom_time',
				bottom_time : value,
				depth_selected : depth
			},
			success : function (result) {
				$('#surface_int_select').html(result);
				$('#surface_int_select').prop("disabled", false);
				validation();
			}
		});

	})

	// See if all drop downs have selected values, before allowing user to press "Add" or "Save" Dive
	$('#surface_int_select').change(
		function() {
			validation();
	})

	//ajax function for AddDive selection to update graph and final calculations
	$('#addDiveForm').on('submit',
	function (e) {
		e.preventDefault();
		//DEBUG MODE
		var debug = false;
		if(debug == true){
			var formValues = "action=debugMode&" + $('#addDiveForm').serialize();
			$.ajax({
				type : "POST",
				url : "testCases.php",
				data : formValues,
				success : function (data) {
					console.log(data);
				},
				error : function () {
					console.log("failed");
				}
			});
			
		}
		else{
			var formValues = "action=addingDive&" + $('#addDiveForm').serialize() + "&pid=" + profileID;
			$.ajax({
				type : "POST",
				url : "ajax_handler.php",
				data : formValues,
				success : function (data) {
					// Show list of dives
					$('#dives').html(data);

					// Automatically assumes the state of editing the just added dive
					$('#addDive').text('Save Dive');
					$('#deleteDive').show();
					$('#finish').prop("disabled", false);
					$('#diveNumber').val($('input:radio[name=diveRadio]:checked').val());

					var latestDiveNum = $("input[name='diveRadio']:last").val();

					if ($('#diveNumber').val() == latestDiveNum) {
						$('#deleteDive').show();
					}
					else {
						$('#deleteDive').hide();
					}

					drawChart(profileID);

					// User selects a dive to edit
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
								/**
								* Reloads the Bottom Time Field so the correct options are displayed
								*/
								value = result['depth'];
								displayBottomTime(value);
								
								var time = result['time'];
								displaySurfInt(value, time);
								
								// Updates drop down values based on the dive selected to edit
								$('#bottom_time_select').prop("disabled", false);
								$('#surface_int_select').prop("disabled", false);
								$('#depth_select option:selected').attr("selected",null);
								$('#depth_select option[value=' + result['depth'] + ']').attr("selected", "selected");
								$('#bottom_time_select option:selected').attr("selected", null);
								$('#bottom_time_select option[value=' + result['time'] + ']').attr("selected", "selected");
								$('#surface_int_select option:selected').attr("selected", null);
								$('#surface_int_select option[value=' + result['surf_int'] + ']').attr("selected", "selected");
								$('#diveNumber').val(result['dive_num']);

								// Only show delete dive if it is the last dive
								if ($('#diveNumber').val() == latestDiveNum) {
									$('#deleteDive').show();
								}
								else {
									$('#deleteDive').hide();
								}

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
	
	// User wants to add new dive
	$('#newDive').click(
	function () {
		$.ajax({
			type : 'POST',
			url : 'ajax_handler.php',
			data : {
				action : "refresh",
			},
			success : function (result) {
				// Resets all related elements to initial state
				$("input[name='diveRadio'").prop("checked", false);
				$('#depth_select').html(result);
				$('#bottom_time_select').empty();
				$('#bottom_time_select').prop("disabled", true);
				$('#surface_int_select').empty();
				$('#surface_int_select').prop("disabled", true);
				$('#safety_depth_select').find('option:first').prop('selected', 'selected');
				$('#safety_time_select').prop("disabled", true);
				$("input[name='defSS']").prop("checked", false);
				$('#diveNumber').val(0);

				$('#deleteDive').hide();
				$('#addDive').prop("disabled", true);
				$('#addDive').text('Add Dive');
			}
		});
	})

	// Default Safety stop option
	$("input[name='defSS']").change(
		function() {
			if (this.checked) {
				$('#safety_depth_select').val(15);
				if ($('#safety_time_select').is(':disabled')) {
					$('#safety_time_select').prop("disabled", false);
				}
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

	$('#safety_depth_select').change(
		function(){
			if ($(this).val() == 0) {
				$('#safety_time_select').prop("disabled", true);
			}
			else {
				$('#safety_time_select').prop("disabled", false);
			}
	})

	// Deleting a dive
	$('#delete').click(
		function(){
			$('#deleteModal').hide();
			var dn = $('#diveNumber').val();

			$.ajax({
				type : 'POST',
				url : 'ajax_handler.php',
				data : {
					action : 'delete_lastest_dive',
					diveNum : dn, // need this to get remaining dives after delete
					profileID : profileID
				},
				success : function (data) {
					// if data is "", then it means there are no more dives
					if (data != "") {
						$('#dives').html(data);
						$('#addDive').text('Save Dive');
						$('#deleteDive').show();
						$('#finish').prop("disabled", false);
						$('#diveNumber').val($('input:radio[name=diveRadio]:checked').val());

						var latestDiveNum = $("input[name='diveRadio']:last").val();
						updateDD(latestDiveNum); // Updates the drop down values

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
										/**
										* Reloads the Bottom Time Field so the correct options are displayed
										*/
										value = result['depth'];
										displayBottomTime(value);
										
										var time = result['time'];
										displaySurfInt(value, time);
										
										$('#bottom_time_select').prop("disabled", false);
										$('#surface_int_select').prop("disabled", false);
										$('#depth_select option:selected').attr("selected",null);
										$('#depth_select option[value=' + result['depth'] + ']').attr("selected", "selected");
										$('#bottom_time_select option:selected').attr("selected", null);
										$('#bottom_time_select option[value=' + result['time'] + ']').attr("selected", "selected");
										$('#surface_int_select option:selected').attr("selected", null);
										$('#surface_int_select option[value=' + result['surf_int'] + ']').attr("selected", "selected");
										$('#diveNumber').val(result['dive_num']);

										if ($('#diveNumber').val() == latestDiveNum) {
											$('#deleteDive').show();
										}
										else {
											$('#deleteDive').hide();
										}

										$('#addDive').text('Save Dive');
									}
								});
						})
					}
					else {
						// there are no more dives, so program assumes initial state where user has to add a dive
						$.ajax({
							type : 'POST',
							url : 'ajax_handler.php',
							data : {
								action : "refresh",
							},
							success : function (result) {
								drawChart(profileID);
								$('#dives').html(data);
								$('#depth_select').html(result);
								$('#bottom_time_select').empty();
								$('#bottom_time_select').prop("disabled", true);
								$('#surface_int_select').empty();
								$('#surface_int_select').prop("disabled", true);
								$('#safety_time_select').prop("disabled", true);
								$("input[name='defSS']").prop("checked", false);
								$('#diveNumber').val(0);

								$('#deleteDive').hide();
								$('#addDive').prop("disabled", true);
								$('#addDive').text('Add Dive');
							}
						});
					}
				}
			});
	})
	
	// Used for when a user deletes a dive, so must update drop down options based on the previous dive (which becomes the new last dive)
	function updateDD(diveNum) {
		$.ajax({
			type : 'POST',
			url : 'ajax_handler.php',
			data : {
				action : 'select_dive_to_edit',
				diveNum : diveNum,
				profileID : profileID
			},
			dataType : "json",
			success : function(result) {
				$('#depth_select option:selected').attr("selected",null);
				$('#depth_select option[value=' + result['depth'] + ']').attr("selected", "selected");
				$('#bottom_time_select option:selected').attr("selected", null);
				$('#bottom_time_select option[value=' + result['time'] + ']').attr("selected", "selected");
				$('#surface_int_select option:selected').attr("selected", null);
				$('#surface_int_select option[value=' + result['surf_int'] + ']').attr("selected", "selected");
				$('#diveNumber').val(result['dive_num']);

				$('#deleteDive').show();
			}
		});
	}
	
})

// Organized function for displaying bottom time.
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
			$('#bottom_time_select').html(result);

		}
	});
}

// Similar to displayBottomTime, but for Surface Interval
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

// Makes sure that all three necessary drop downs have selected values before user can save/edit dive
function validation() {
	if ($('#depth_select').val() == null || $('#bottom_time_select').val() == null || $('#surface_int_select').val() == null) {
		$('#addDive').prop("disabled", true);
	}
	else {
		$('#addDive').prop("disabled", false);
	}
}
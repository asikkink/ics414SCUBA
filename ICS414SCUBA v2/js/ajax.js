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
		var formValues = $('#addDiveForm').serialize() + "&action='addingDive'";
		//alert(formValues);
		$.ajax({
			type : "POST",
			url : "ajax_handler.php",
			data : {
				action : 'addingDive',
				depth : 'true'
			},
			success : function (data) {
		//		$('#surface_int_select').html(result);
				//console.log("hello");
				alert(data);
			},
			error : function () {
				//console.log("failed");
			}
		});

	})

})
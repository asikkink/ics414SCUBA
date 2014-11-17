$(document).ready(
	function(){
		//ajax called at dom initialization to update depth field with options
		$.ajax  ({
			type: 'POST',
			url: 'ajax_handler.php',
			data: {init: 'true'},
			success: function(result){
				//alert(result);
				$('#depth_select').html(result);							
			}
		});
	
	
		//ajax function for depth selection to update bottom time field options
		$('#depth_select').change(
			function(){
				value = $('#depth_select').val();
				//alert(value);
				$.ajax  ({
					type: 'POST',
					url: 'ajax_handler.php',
					data: {depth: value},
					success: function(result){
						//alert(result);
						$('#bottom_time_select').html(result);
									
					}
				});			
			}		
		)
		
		//ajax function for bottom time selection to update surface int field options
		$('#bottom_time_select').change(
			function(){
				value = $('#bottom_time_select').val();
				depth = $('#depth_select').val();
				//alert(value);
				$.ajax  ({
					type: 'POST',
					url: 'ajax_handler.php',
					data: {bottom_time: value, depth_selected: depth},
					success: function(result){
						//alert(result);
						$('#surface_int_select').html(result);
									
					}
				});
			
			}
		

		)


	}
)
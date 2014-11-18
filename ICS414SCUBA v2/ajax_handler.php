<?php
//initialize the database connection
$db = mysqli_connect('localhost', 'bigoli', 'pasta', 'dive_table');
if ($db->connect_errno) echo'Could not open database connection.';
if(isset($_POST['action'])){
	$action = $_POST['action'];
	switch($action){
	case 'initialize': initialize($db);break;
	case 'select_max_depth': selectDepth($db); break;
	case 'select_bottom_time': selectBottomTime($db); break;
	case 'addingDive': addDive($db); break;
	default: echo "No POST data sent to server."; break;
	}
}else{
	echo "No POST data sent to server.";
}
//set the initial values for the max depth field
function initialize($db){
	if(isset($_POST['init'])){
		//set sql statement
		$sql = "SELECT DISTINCT `depth` from `bottom_time` ORDER BY `depth` ASC";
		//see if there is a result when we run the query
		if(!$result = mysqli_query($db, $sql)){
			//if no result, there was an error
			echo "MySQL error:".mysqli_error();
		}
		//if there is no mysql error, see if any rows were returned from the query
		else if(mysqli_num_rows($result)){
			$response = "";
			for($i=0;$i<mysqli_num_rows($result);$i++){
				$data = mysqli_fetch_assoc($result);
				$response .= "<option value='{$data['depth']}'>{$data['depth']}</option>";
			}
			echo $response;
		}
		//send error
	}else echo "No rows returned by database.";
}
//if a specific depth has been selected, retrieve the bottom times for that depth
function selectDepth($db){
	if(isset($_POST['depth'])){
		//set sql statement
		$sql = "SELECT `time` from `bottom_time` WHERE `depth` = '{$_POST['depth']}' ORDER BY `time` ASC";
		//see if there is a result when we run the query
		if(!$result = mysqli_query($db, $sql)){
			//if no result, there was an error
			echo "MySQL error:".mysqli_error();
		}
		//if there is no mysql error, see if any rows were returned from the query
		else if(mysqli_num_rows($result)){
			$response = "";
			for($i=0;$i<mysqli_num_rows($result);$i++){
				$data = mysqli_fetch_assoc($result);
				$response .= "<option value='{$data['time']}'>{$data['time']}</option>";
			}
			echo $response;
		}
		//send error
	}else echo "No rows returned by database.";
}
//if a specific bottom time has been selected, retrieve the surface intervals for that depth
function selectBottomTime($db){
	if(isset($_POST['bottom_time'])){
		//set sql statement
		$sql = "SELECT `end_time` from `surface_interval` WHERE `init_pressure_group` IN ( SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$_POST['depth_selected']}' AND `time` = '{$_POST['bottom_time']}') ORDER BY `end_time` ASC";
		//see if there is a result when we run the query
		if(!$result = mysqli_query($db, $sql)){
			//if no result, there was an error
			echo "MySQL error:".mysqli_error();
		}
		//if there is no mysql error, see if any rows were returned from the query
		else if(mysqli_num_rows($result)){
			$response = "";
			for($i=0;$i<mysqli_num_rows($result);$i++){
				$data = mysqli_fetch_assoc($result);
				$response .= "<option value='{$data['end_time']}'>{$data['end_time']}</option>";
			}
			echo $response;
		}
		//send error
	}else echo "No rows returned by database.";
}
//When submit button is pressed, manage current input
function addDive($db){
	
	getFinalPressureGroup($db);
	getFinalPressureGroup($db);
	getPressureGroup($db);
}


function getFinalPressureGroup($db){
	if(isset($_POST['surface_int_select'])){
		//$message = "wrong answer";
		//echo "YAY AJAX!!!";
		
		//$sql = "SELECT `final_pressure_group` from `surface_interval` WHERE `init_pressure_group` IN ( SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$_POST['depth_selected']}' AND `time` = '{$_POST['bottom_time']}') ORDER BY `end_time` ASC";
		/*
		======================================================================
		Properly get the final pressure group using initial pressure group 
		
		======================================================================
		*/
		
		
		$sql = "SELECT `final_pressure_group` FROM `surface_interval` WHERE `end_time` = '{$_POST['surface_int_select']}'";
		//see if there is a result when we run the query
		if(!$result = mysqli_query($db, $sql)){
			//if no result, there was an error
			echo "MySQL error:".mysqli_error($db);
		}
		//if there is no mysql error, see if any rows were returned from the query
		else if(mysqli_num_rows($result)){
			$response = "";
			for($i=0;$i<mysqli_num_rows($result);$i++){
				$data = mysqli_fetch_assoc($result);
				$response = $data;
			}
			echo $response['final_pressure_group'];
		}
	}
	else echo "no surface int!!";

}

function getPressureGroup($db){
	if(isset($_POST['depth_select']) && isset($_POST['bottom_time_select'])){
		$depth = $_POST['depth_select'];
		$time = $_POST['bottom_time_select'];
		
		//$sql = "SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$_POST['depth_select']}' AND `time` = '{$_POST['bottom_time_select']}'";
		$sql = "select `pressure_group` from `bottom_time` where `depth` = '{$_POST['depth_select']}' and `time` = '{$_POST['bottom_time_select']}'";
		/*
		=============================================================
		What we've learned today 11-18 1:22AM
		PHP HATES US
		Apparently the two queries give different results
		` <- This matters... 2 hours to discover
		
		==============================================================
		
		*/
		if(!$result = mysqli_query($db, $sql)){
			echo "MySQL error:".mysqli_error($db);
		}
		else if(mysqli_num_rows($result)){
			$response = "";
			$data = mysqli_fetch_assoc($result);
			$response = $data;
			echo $response['pressure_group'];
		}
		
	}
	else echo "no depth selected!!";
}


?>
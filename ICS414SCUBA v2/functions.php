<?php
//File that includes all of the functions for our dive planner app!


//set the initial values for the max depth field
function initialize($db,$POST){
	if(isset($POST['init'])){
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
function selectDepth($db, $POST){
	if(isset($POST['depth'])){
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
function selectBottomTime($db, $POST){
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
function addDive($db, $POST){
	$profileID = 1;
	$diveNum = getDiveNum($db, $profileID);
	
	//get initial PG
	$initialPG = getInitialPG($db, $profileID, $diveNum-1);
	$postDivePG = getPostDivePG($db, $POST['depth_select'], $POST['bottom_time_select']);
	$postSurfacePG = getPostSurfaceIntPG($db, $postDivePG, $POST['surface_int_select']);
	//insert to table
	$sql = "INSERT INTO `dives` VALUES ('$profileID', '$diveNum', '$initialPG', '{$POST['depth_select']}', 
	'{$POST['bottom_time_select']}', '$postDivePG', '{$POST['surface_int_select']}', '$postSurfacePG') ";
	mysqli_query($db, $sql);
	
	echo "id: $profileID, diveNum: $diveNum, depth:{$POST['depth_select']}, time: {$POST['bottom_time_select']}, surfInt:{$POST['surface_int_select']}";
	
	
}

function getDiveNum($db, $profileID){
	$sql = "SELECT `dive_num` FROM `dives` WHERE `profile_id` = '$profileID'";
	
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	
	//see how many rows were returned
	$num_dives = mysqli_num_rows($result);
	
	//if empty table returned, there is no dive yet
	if($num_dives == 0) $dive_num = 1; 
	//else we have the dive nums
	else {
		$dive_num = $num_dives + 1;
	}
	return $dive_num;
}

function getInitialPG($db, $profileID, $prevDiveNum){
	//if no previous dives, initialize first one
	$sql = "SELECT `post_surf_int_pg` FROM `dives` WHERE `profile_id` = '$profileID' AND `dive_num` = '$prevDiveNum'";
	
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	//if empty table returned, there is no dive yet
	if(mysqli_num_rows($result) == 0) $init_pressure_group = null; 
	//else we have the post_surf_int_pg
	else {
		//take last dive num and grab post_surf_int_pg from results
		$ipg = mysqli_fetch_assoc($result);
		$last_dive = end($ipg);
		$init_pressure_group = $last_dive;
	}
	
	return $init_pressure_group;	
	
}

function getPostDivePG($db, $depth, $time){

	$sql = "SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '$depth' AND `time` = '$time'";
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) return "broken";
	else {
		$pdpg = mysqli_fetch_assoc($result);
		return $pdpg['pressure_group'];
	}
	
}


function getPostSurfaceIntPG($db, $postDivePG, $surfInt) {
	
	$sql = "SELECT `final_pressure_group` FROM `surface_interval` WHERE `init_pressure_group` = '$postDivePG' AND `end_time` = '$surfInt'";
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) return "broken";
	else {
		$psipg = mysqli_fetch_assoc($result);
		return $psipg['final_pressure_group'];
	}
	
}



// function getFinalPressureGroup($db){
	// if(isset($_POST['surface_int_select'])){
		// //$message = "wrong answer";
		// //echo "YAY AJAX!!!";
		
		// //$sql = "SELECT `final_pressure_group` from `surface_interval` WHERE `init_pressure_group` IN ( SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$_POST['depth_selected']}' AND `time` = '{$_POST['bottom_time']}') ORDER BY `end_time` ASC";
		// /*
		// ======================================================================
		// Properly get the final pressure group using initial pressure group 
		
		// ======================================================================
		// */
		
		
		// $sql = "SELECT `final_pressure_group` FROM `surface_interval` WHERE `end_time` = '{$_POST['surface_int_select']}'";
		// //see if there is a result when we run the query
		// if(!$result = mysqli_query($db, $sql)){
			// //if no result, there was an error
			// return "MySQL error:".mysqli_error($db);
		// }
		// //if there is no mysql error, see if any rows were returned from the query
		// else if(mysqli_num_rows($result)){
			// $response = "";
			// for($i=0;$i<mysqli_num_rows($result);$i++){
				// $data = mysqli_fetch_assoc($result);
				// $response = $data;
			// }
			// return $response['final_pressure_group'];
		// }
	// }
	// else return "no surface int!!";

// }

// function getPressureGroup($db){
	// if(isset($_POST['depth_select']) && isset($_POST['bottom_time_select'])){
		// $depth = $_POST['depth_select'];
		// $time = $_POST['bottom_time_select'];
		
		// //$sql = "SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$_POST['depth_select']}' AND `time` = '{$_POST['bottom_time_select']}'";
		// $sql = "select `pressure_group` from `bottom_time` where `depth` = '{$_POST['depth_select']}' and `time` = '{$_POST['bottom_time_select']}'";
		// /*
		// =============================================================
		// What we've learned today 11-18 1:22AM
		// PHP HATES US
		// Apparently the two queries give different results
		// ` <- This matters... 2 hours to discover
		
		// ==============================================================
		
		// */
		// if(!$result = mysqli_query($db, $sql)){
			// return "MySQL error:".mysqli_error($db);
		// }
		// else if(mysqli_num_rows($result)){
			// $response = "";
			// $data = mysqli_fetch_assoc($result);
			// $response = $data;
			// return $response['pressure_group'];
		// }
		
	// }
	// else return "no depth selected!!";
// }




?>
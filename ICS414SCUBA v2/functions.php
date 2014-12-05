<?php
//File that includes all of the functions for our dive planner app!


//set the initial values for the max depth field
function initialize($db,$POST){
	if(isset($POST['init'])){
		refreshVals($db);
		//send error
	}else echo "No rows returned by database.";
}

function refreshVals($db) {
	//set sql statement
	$sql = "SELECT DISTINCT `depth` from `bottom_time` ORDER BY `depth` ASC";
	//see if there is a result when we run the query
	if(!$result = mysqli_query($db, $sql)){
		//if no result, there was an error
		echo "MySQL error:".mysqli_error();
	}
	//if there is no mysql error, see if any rows were returned from the query
	else if(mysqli_num_rows($result)){
		$response = "<option value='' selected disabled>Choose...</option>";
		for($i=0;$i<mysqli_num_rows($result);$i++){
			$data = mysqli_fetch_assoc($result);
			$response .= "<option value='{$data['depth']}'>{$data['depth']}</option>";
		}
		echo $response;
	}
}


/**If a specific depth has been selected, retrieve the bottom times for that depth
*  Field affected: Bottom Time(minutes)
====================================================================================
*  [Need Fix] Condition: Based on initial PG and depth, limit selection to be less than actual_bottom_time
====================================================================================
*/
function selectDepth($db, $POST){
	if(isset($POST['depth'])){
		//set sql statement
		$sql = "SELECT `time` from `bottom_time` WHERE `depth` = '{$POST['depth']}' ORDER BY `time` ASC";
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
/**If a specific bottom time has been selected, retrieve the surface intervals for that depth
*  Field affected: Surface Interval (minutes)
*/
function selectBottomTime($db, $POST){
	if(isset($_POST['bottom_time'])){
		//set sql statement
		$sql = "SELECT `start_time`, `end_time` from `surface_interval` WHERE `init_pressure_group` IN ( SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$POST['depth_selected']}' AND `time` = '{$POST['bottom_time']}') ORDER BY `end_time` ASC";
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
				$response .= "<option value='{$data['end_time']}'>{$data['start_time']} - {$data['end_time']}</option>";
			}
			echo $response;
		}
		//send error
	}else echo "No rows returned by database.";
}
/**When submit button is pressed, manage add dive to dive table
*  Test: echo's dive row back to client console
*  Everything goes on here for testing
*/
function addDive($db, $POST){
	//references
	$profileID = 1;
	$depth = $POST['depth_select'];
	$time = $POST['bottom_time_select'];
	$surfInt = $POST['surface_int_select'];
	$diveNum = $POST['diveNumber'];

	if ($diveNum == 0) {
		// NEW DIVE
		$diveNum = getDiveNum($db, $profileID);
		//get current dive values
		$initialPG = getInitialPG($db, $profileID, $diveNum-1);
		
		//in order to get an accurate pg, need to calculate the residual(from last dive) + time and send that to the getPostDivePG function
		$prevDiveResidual = getPrevDiveResidual($db, $profileID, $diveNum-1);
		$postDivePG = getPostDivePG($db, $depth, $time+$prevDiveResidual);
		
		
		$postSurfacePG = getPostSurfaceIntPG($db, $postDivePG, $surfInt);
		
		/**Include Residual Time
		* Residual time is calculated as postSurfacePG + depth -> residual time and Total bottom time*/
		//=========================
		$residualTime = getResidualTime($db, $postSurfacePG, $depth);
		$totalTime = $residualTime + $time;
		//$time = $residualTime + $time;
		//echo $residualTime;
		//===========================
		
		
		//insert values into table
		$sql = "INSERT INTO `dives` VALUES ('$profileID', '$diveNum', '$initialPG', '$depth', 
		'$time', '$postDivePG', '$surfInt', '$postSurfacePG', '$residualTime') ";
	}
	else {
		// EDIT EXISTING DIVE
		$diveNum = $POST['diveNumber'];
		$sql = "UPDATE `dives` SET `depth` = '$depth', `time` = '$time', `surf_int` = '$surfInt' WHERE `profile_id` = '$profileID' AND `dive_num` = '$diveNum'";
	}

	mysqli_query($db, $sql);
	
	//echo "id: $profileID, diveNum: $diveNum, depth:$depth, time: $time, surfInt:$surfInt";
	//echo "\tInitialPG: $initialPG, PostDivePG: $postDivePG, PostSurfIntPG: $postSurfacePG\n";
	
	// NEED TO CALL A FUNCTION TO UPDATE PRESSURE GROUPS HERE

	$diveInfo = getDives($db, $diveNum, $profileID);

	echo $diveInfo;
}

/**Gets the LASTEST dive number
*  Used: AddDive will insert the next incremented dive number to dives
*/
function getDiveNum($db, $profileID){
	$sql = "SELECT `dive_num` FROM `dives` WHERE `profile_id` = '$profileID'";
	
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	$num_dives = mysqli_num_rows($result);
	//if empty table returned, there is no dive yet
	if($num_dives == 0) $dive_num = 1; 
	else {
		$dive_num = $num_dives + 1;
	}
	return $dive_num;
}

/**Gets the post_surface_int_pg from previous dive
*  Used: AddDive sets the Initial Pressure group of current dive.
*/
function getInitialPG($db, $profileID, $prevDiveNum){
	//if no previous dives, initialize first one
	$sql = "SELECT `post_surf_int_pg` FROM `dives` WHERE `profile_id` = '$profileID' AND `dive_num` = '$prevDiveNum'";
	error_log("getInitPG: $sql");
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
/**Get the post dive pressure group
*  The $time will be the Total time 
*  
*/
function getPostDivePG($db, $depth, $time){

	$sql = "SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '$depth' AND `time` >= '$time'";
	error_log("getPostDivePG: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) return "broken";
	else {
		$pdpg = mysqli_fetch_assoc($result);
		return $pdpg['pressure_group'];
	}
}


/**Get residual time. Pressure group -> Depth = Residual time
*  Used: getPostDivePG will need to add residual time to $time to get total time.
* EDIT: Residual time is calculated with the PG after the surface interval...
*/
function getResidualTime($db, $postSurfacePG, $depth){
	if($postSurfacePG == null) return 0;
	$sql = "SELECT `residual_time` FROM `residual_time` WHERE `pressure_group` = '$postSurfacePG' and `depth` = '$depth'";
	error_log("getResidTime: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	
	if(mysqli_num_rows($result) == 0) return "broken";
	else {
		$residualRow = mysqli_fetch_assoc($result);
		return $residualRow['residual_time'];
	}
}


/**Get the post surface interval pressure group
*
*/
function getPostSurfaceIntPG($db, $postDivePG, $surfInt) {
	
	$sql = "SELECT `final_pressure_group` FROM `surface_interval` WHERE `init_pressure_group` = '$postDivePG' AND `end_time` >= '$surfInt'";
	error_log("getPostSurfaceIntPG: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) return "broken";
	else {
		$psipg = mysqli_fetch_assoc($result);
		return $psipg['final_pressure_group'];
	}
	
}


function getDives($db, $diveNum, $profileID) {

	$sql = "SELECT `depth`, `time`, `dive_num` FROM `dives` WHERE `profile_id` = '$profileID' ORDER BY `dive_num` ASC";
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	$numRows = mysqli_num_rows($result);
	if($numRows == 0) return "broken";
	else if($numRows){
		$diveInfo = "";
		$checked = false;
		for($i=1;$i<=$numRows;$i++){
			$data = mysqli_fetch_assoc($result);
			$diveInfo .= "<input type='radio' name='diveRadio' id='$i' value='$i'";
			if (($data['dive_num'] == $diveNum || $i == $numRows) && !$checked) {
				$diveInfo .= " checked";
				$checked = true;
			}
			$diveInfo .= "> <strong>Dive $i:</strong> {$data['depth']} ft. for {$data['time']} min.<br>";
		}
		return $diveInfo;
	}
}

function showDive($db, $POST) {
	$profileID = 1;
	
	$sql = "SELECT `depth`, `time`, `surf_int`, `dive_num` FROM `dives` WHERE `profile_id` = '$profileID' AND `dive_num` = '{$POST['diveNum']}'";

	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) echo 0;
	else {
		$test = mysqli_fetch_assoc($result);
		//call select Depth to correct bottom time field
		
		
		echo json_encode($test);
	}
	closeDB($db);

}

//Get dive data
function getDiveData($db){
	$profileID = 1;
	
	$sql = "SELECT `dive_num`, `depth`, `time`, `surf_int`, `post_dive_pg`, `post_surf_int_pg`, `residual_time` FROM `dives` WHERE `profile_id` = '$profileID'";
	
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) echo 0;
	else {
		$json = mysqli_fetch_all($result, MYSQLI_ASSOC);
		echo json_encode($json);
	}
	closeDB($db);

}

//Simple close db connection function
function closeDB($db){
	mysqli_close($db);
}

function getPrevDiveResidual($db, $profileID, $prevDiveNum){
$sql = "SELECT `residual_time` FROM `dives` WHERE `profile_id` = '$profileID' AND `dive_num` = '$prevDiveNum'";
	error_log("getPrev: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	//if empty table returned, there is no dive yet
	if(mysqli_num_rows($result) == 0) $residual_time = 0; 
	//else we have the residual time from the previous dive
	else {
		//take last dive num and grab post_surf_int_pg from results
		$prevDive = mysqli_fetch_assoc($result);
		$residual_time = $prevDive['residual_time'];
		error_log($residual_time);
	}
	return $residual_time;	
}


?>